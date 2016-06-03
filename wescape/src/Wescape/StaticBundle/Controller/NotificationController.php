<?php

namespace Wescape\StaticBundle\Controller;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MapController
 *
 * @package Wescape\StaticBundle\Controller
 *
 * @NamePrefix("notification_")
 */
class NotificationController extends Controller
{
    const CLIENT_KEY = "esI6c895NAI:APA91bFlKWKmyFf0n8cIqSTmQw3h0-qMS-NpM28FN_qMErA2MmircpLcrQ8QKty30BLhL1pGqmxG6Fqz3uwLrRHxGgEMcX9xgSmIRIBt5Ti2UUhK3sDgDYIa9aBNOoQlnvGWNlQnhQoD";
    const SERVER_KEY = "AIzaSyCCFnEuXrI_e68iwmaPlFMAJGwq90-OHAA";

    /**
     * @Route("/notification")
     *
     * @return Response
     */
    public function triggerNotificationAction() {

        $ch = curl_init('fcm.googleapis.com/fcm/send');

        $data = [
            "to"           => self::CLIENT_KEY,
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

        $options = [
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_HTTPHEADER     => [
                'Content-type: application/json',
                'Authorization: key=' . self::SERVER_KEY
            ],
            CURLOPT_POSTFIELDS     => $data_json,
            CURLOPT_RETURNTRANSFER => TRUE,

        ];

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        var_dump($response);
        die();
    }
}