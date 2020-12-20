<?php

namespace App\Controller\API;

use App\Form\UserType;
use App\Form\PasswordFormType;
use App\Entity\User;
use App\Entity\Statut;
use App\Entity\Message;
use App\Entity\Invitation;
use App\Entity\Groupe;
use App\Entity\Notification;
use App\Entity\TypeNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;

/**
 * @Route("/notification", name="notif")
 */
class NotificationController extends AbstractController
{
    private EntityManager $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }

    /**
     * @author ZONCHELLO Sébastien
     * Permet de marquer une notification d'un message comme lue
     * @Route("/readNotifMsg", name="notification_readNotifMsg")
     */
    public function readNotifMsg(Request $request)
    {

        $groupeId = $request->get("groupeId");

        if ($groupeId && is_numeric($groupeId) && $this->entityManager->getRepository(Notification::class)->IsNotRead($groupeId,$this->getUser()->getId())) {

            $notification = $this->entityManager->getRepository(Notification::class)->getNotification($groupeId,$this->getUser()->getId());
            $notification->setNbMsg(0);
            $notification->setEstLue(true);
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
            

            return new JsonResponse(["statut" => "ok",
                                     "nbNotifs" => $this->entityManager->getRepository(Notification::class)->countMsgNotRead($this->getUser()->getId())[0][1]]);

        } else {
            return new JsonResponse(["statut" => "nok",
                                     "message" => "Arguments invalides"]);
        }

    }

    /**
     * @author ZONCHELLO Sébastien
     * Permet de marquer une notification d'un groupe comme lue
     * @Route("/readNotifGrp", name="notification_readNotifGrp")
     */
    public function readNotifGrp(Request $request)
    {

        $groupeId = $request->get("groupeId");

        if ($groupeId && is_numeric($groupeId) && $this->entityManager->getRepository(Notification::class)->IsNotRead($groupeId,$this->getUser()->getId())) {

            $notificationsrepo = $this->entityManager->getRepository(Notification::class);

            $notification = $notificationsrepo->getNotification($groupeId,$this->getUser()->getId());
            $notification->setNbMsg(0);
            $notification->setEstLue(true);
            $notification->setTypeNotification($this->entityManager->getRepository(TypeNotification::class)->find(2));

            $this->entityManager->persist($notification);
            $this->entityManager->flush();

            return new JsonResponse(["statut" => "ok",
                                     "nbNotifs" => $notificationsrepo->countGrpNotRead($this->getUser()->getId())[0][1]]);

        } else {
            return new JsonResponse(["statut" => "nok",
                                     "message" => "Arguments invalides"]);
        }

    }


}