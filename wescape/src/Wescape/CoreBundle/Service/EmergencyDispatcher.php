<?php

namespace Wescape\CoreBundle\Service;


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Wescape\CoreBundle\Entity\User;

class EmergencyDispatcher
{
    /** @var  EntityManager */
    private $em;

    /** @var  ContainerInterface */
    private $container;

    /** @var  string */
    private $firebaseSecret;

    /**
     * LOSManagerService constructor.
     *
     * @param EntityManager      $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $entityManager, ContainerInterface $container) {
        $this->em = $entityManager;
        $this->container = $container;
        $this->firebaseSecret = $this->container->getParameter("firebase_secret");
    }

    public function notifyAll() {
        /** @var User[] $users */
        $users = $this->em->createQueryBuilder()
            ->select('u')
            ->from('CoreBundle:User','u')
            ->where('u.deviceKey is not null')
            ->getQuery()
            ->execute();

        $report = [
            'successes' => 0,
            'failures' => 0
        ];
        
        foreach ($users as $user) {
            $response = $this->sendPushNotification($user->getDeviceKey());
            if ($response['success']) {
                $report['successes'] += 1;
            } else {
                $report['failures'] += 1;
            }
        }
        
        return $report;
    }

    private function sendPushNotification($deviceKey) {
        $data = [
            "to"           => $deviceKey,
            "priority"     => "high",
            "notification" => [
                "body"  => "Situazione di emergenza!",
                "title" => "Emergenza Wescape",
                "click_action" => "EMERGENCY_ACTION",
            ],
            "data"         => [
                "emergency" => TRUE
            ]
        ];
        $data_json = json_encode($data);

        $params = [
            'http' => [
                'method' => 'POST',
                'content' => $data_json,
                'header' => [
                    'Content-type: application/json',
                    'Authorization: key=' . $this->firebaseSecret
                ]
            ]
        ];

        $context = stream_context_create($params);
        $responseContent = file_get_contents('http://fcm.googleapis.com/fcm/send', false, $context);

        return json_decode($responseContent, true);
    }

}