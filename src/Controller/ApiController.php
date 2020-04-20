<?php

namespace App\Controller;

use App\Entity\Phone;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends AbstractController
{
    /**
     * @Route("/api", name="api")
     */
    public function index(SerializerInterface $serializer)
    {
        $phone = new Phone();
        $phone->setName('iPhone X');
        $phone->setPrice(1000);
        $phone->setDescription('Un superbe iphone');

        $data = $serializer->serialize($phone, 'json');

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}