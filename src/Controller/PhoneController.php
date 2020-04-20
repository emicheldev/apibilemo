<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/api/phones")
 */

class PhoneController extends AbstractController
{
     /**
     * @Route("/{page<\d+>?1}", name="list_phone", methods={"GET"})
     */
    public function index(Request $request,PhoneRepository $phoneRepository, SerializerInterface $serializer)
    {
        $page = $request->query->get('page');
        if(is_null($page) || $page < 1) {
            $page = 1;
        }
        $limit = 10;
        $phones = $phoneRepository->findAllPhones($page, $limit);

        $data = $serializer->serialize($phones, 'json');

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }


     /**
     * @Route("/{id}", name="show_phone", methods={"GET"})
     */
    public function show(Phone $phone, PhoneRepository $phoneRepository, SerializerInterface $serializer)
    {
        $phone = $phoneRepository->find($phone->getId());
        $data = $serializer->serialize($phone, 'json');
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
