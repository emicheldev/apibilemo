<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/phones")
 */

class PhoneController extends AbstractController
{
     /**
     * @Route("/", name="list_phone", methods={"GET"})
     */
    public function index(PhoneRepository $phoneRepository, SerializerInterface $serializer)
    {
        $phones = $phoneRepository->findAll();
        $data = $serializer->serialize($phones, 'json');

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
