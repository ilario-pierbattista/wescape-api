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
 * @NamePrefix("static_map_")
 */
class MapController extends Controller
{
    const MAP_FILE_EXTENSION = ".jpg";

    /**
     * @Route("/maps/{floor}")
     *
     * @param $floor
     *
     * @return Response
     */
    public function getAction($floor) {
        $mapDir = realpath($this->get("kernel")->getRootDir() .
            DIRECTORY_SEPARATOR . ".." .
            DIRECTORY_SEPARATOR . "maps");
        $fileName = $floor . self::MAP_FILE_EXTENSION;
        $filePath = realpath($mapDir . DIRECTORY_SEPARATOR . $fileName);
        if (file_exists($filePath)) {
            $image = file_get_contents($filePath);
            $headers = [
                "Content-Type" => "image/jpg"
            ];
            $response = new Response($image, 200, $headers);
        } else {
            $response = new Response(NULL, 404);
        }
        return $response;
    }
}