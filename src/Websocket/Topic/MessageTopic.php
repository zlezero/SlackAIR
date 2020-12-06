<?php

namespace App\Websocket\Topic;

use App\Entity\Message;
use App\Entity\User;
use App\Entity\Groupe;
use App\Entity\Invitation;
use Gos\Bundle\WebSocketBundle\Client\ClientManipulatorInterface;
use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\Topic\SecuredTopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class MessageTopic implements TopicInterface, SecuredTopicInterface
{

    private ClientManipulatorInterface $clientManipulator;
    private EntityManager $entityManager;


    public function __construct(ClientManipulatorInterface $clientManipulator, EntityManagerInterface $entityManager)
    {
        $this->clientManipulator = $clientManipulator;
        $this->entityManager = $entityManager;
    }

    /**
     * This will receive any Subscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     *
     * @return void
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        
        $user = $this->clientManipulator->getClient($connection)->getUser();
        $channelId = $request->getAttributes()->get('idChannel');

        if ($user == NULL || !$this->entityManager->getRepository(Invitation::class)->isUserInChannel($channelId, $user->getId())) {
            $connection->close();
        }

    }

    /**
     * This will receive any unsubscription requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     *
     * @return void
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        // This will broadcast the message to ALL subscribers of this topic.
        //$topic->broadcast(['msg' => $connection->resourceId.' has left '.$topic->getId()]);
    }

    /**
     * This will receive any Publish requests for this topic.
     *
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @param mixed $event
     * @param array $exclude
     * @param array $eligibles
     *
     * @return mixed
     */
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible) {
            
        if(gettype($event) == 'string') {

            $data = json_decode($event);
            $message = $this->entityManager
                            ->getRepository(Message::class)
                            ->find($data->data->message->id);
            $this->broadcastMessage($topic, $message, false);

        } else {
            
            $data = json_decode($event["data"]);
            $user = $this->clientManipulator->getClient($connection)->getUser();

            $user_entity = $this->entityManager
                            ->getRepository(User::class)
                            ->find($user->getId());

            $groupe = $this->entityManager
                            ->getRepository(Groupe::class)
                            ->find($data->channel);

            $message = new Message();
            $message->setTexte($data->message);
            $message->setDateEnvoi(date_create());
            $message->setUserId($user_entity);
            $message->setGroupeId($groupe);
            $message->setEstEfface(false);
            $message->setEstEpingle(false);

            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $this->entityManager->getRepository(Invitation::class)->addNotification($user_entity->getId(), $groupe->getId());

            $this->entityManager->refresh($user_entity);
            $this->broadcastMessage($topic, $message, false);
        }
          
    }

    /**
     * @param ConnectionInterface $conn
     * @param Topic               $topic
     * @param null|string         $payload
     * @param string[]|null       $exclude
     * @param string[]|null       $eligible
     * @param string|null         $provider
     *
     * @return void
     */
    public function secure(?ConnectionInterface $conn, Topic $topic, WampRequest $request, $payload = null, ?array $exclude = [], ?array $eligible = null, ?string $provider = null): void
    {
        // Check input data to verify if connection must be blocked
        if ($request->getAttributes()->has('denied')) {
            throw new FirewallRejectionException('Access denied');
        }

        // Access is granted
    }

    /**
     * Like RPC the name is used to identify the channel
     *
     * @return string
     */
    public function getName(): string
    {
        return 'message.topic';
    }

    private function broadcastMessage(Topic $topic, Message $message, bool $system) {
        $topic->broadcast(['system' => $system, 'message' => $message->getFormattedMessage()]);
    }

}