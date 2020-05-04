<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



/**
 * @Route("/api")
 */

class ClientController extends AbstractController
{
     /**
     * @Route("/clients/{page<\d+>?1}", name="list_client", methods={"GET"})
     */
    public function index(Request $request,ClientRepository $clientRepository, SerializerInterface $serializer)
    {
        $page = $request->query->get('page');
        if(is_null($page) || $page < 1) {
            $page = 1;
        }
        $limit = 10;
        $clients = $clientRepository->findAllClients($page, $limit);

        $data = $serializer->serialize($clients, 'json', [
            'groups' => ['list']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }


     /**
     * @Route("/clients/{id}", name="show_client", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Client $client, ClientRepository $clientRepository,SerializerInterface $serializer)
    {
        $client = $clientRepository->find($client->getId());
        $data = $serializer->serialize($client, 'json', [
            'groups' => ['show']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/clients", name="add_client", methods={"POST"})
     */
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $client = $serializer->deserialize($request->getContent(), Client::class, 'json');
        $errors = $validator->validate($client);

        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }
        $client->setUser($this->getUser());
        $entityManager->persist($client);
        $entityManager->flush();
        $data = [
            'status' => 201,
            'message' => 'Le client a bien été ajouté'
        ];
        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/clients/{id}", name="update_client", methods={"PUT"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function update(Request $request, SerializerInterface $serializer, Client $client, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $userUpdate = $entityManager->getRepository(Client::class)->find($client->getId());
        $data = json_decode($request->getContent());

        foreach ($data as $key => $value){
            if($key && !empty($value)) {
                $name = ucfirst($key);
                $setter = 'set'.$name;
                $userUpdate->$setter($value);
            }
        }
        $errors = $validator->validate($userUpdate);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }
        $entityManager->flush();
        $data = [
            'status' => 200,
            'message' => 'Le client a bien été mis à jour'
        ];
        return new JsonResponse($data);
    }

     /**
     * @Route("/clients/{id}", name="delete_client", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Client $client, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($client);
        $entityManager->flush();
        return new Response(null, 204);
    }
}
