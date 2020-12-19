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

/**
 * @author VATHONNE Thomas
 * Ce websocket permet de diffuser tous les événements relatifs à un channel
 */

class ChannelEventTopic implements TopicInterface
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
    {}

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

        $user = $this->clientManipulator->getClient($connection)->getUser();

        if (is_array($event)) {
            $data = json_decode($event["data"]);
        } else {
            $data = json_decode($event);
            $data = $data->data;
        }

        $channel = $this->entityManager->getRepository(Groupe::class)->findOneBy(['id' => $data->channel]);

        $sendData = array();

        $sendData["channel"] = $data->channel;
        $sendData["channelType"] = $channel->getTypeGroupeId()->getId();

        switch ($data->event->type) {
            case 'startWriting':
                $sendData["event"]["type"] = 'startWriting';
                $sendData["event"]["valeur"] = $user->getId();
                $sendData["event"]["pseudo"] = $user->getPseudo();
                break;
            case 'stopWriting':
                $sendData["event"]["type"] = 'stopWriting';
                $sendData["event"]["valeur"] = $user->getId();
                $sendData["event"]["pseudo"] = $user->getPseudo();
                break;
            case 'channelTitleUpdate':
                $sendData["event"]["type"] = 'channelTitleUpdate';
                $sendData["event"]["valeur"] = $data->channelTitre;
                break;
            case 'channelDescriptionUpdate':
                $sendData["event"]["type"] = 'channelDescriptionUpdate';
                $sendData["event"]["valeur"] = $data->channelDescription;
                break;
            default:
                return;
        }

        $topic->broadcast(["data" => $sendData]);

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
     *//*
    public function secure(?ConnectionInterface $conn, Topic $topic, WampRequest $request, $payload = null, ?array $exclude = [], ?array $eligible = null, ?string $provider = null): void
    {
        // Check input data to verify if connection must be blocked
        if ($request->getAttributes()->has('denied')) {
            throw new FirewallRejectionException('Access denied');
        }

        // Access is granted
    }*/

    /**
     * Like RPC the name is used to identify the channel
     *
     * @return string
     */
    public function getName(): string
    {
        return 'channelevent.topic';
    }

}