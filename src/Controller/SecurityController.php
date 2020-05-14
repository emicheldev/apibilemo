<?php

namespace App\Controller;

use App\Entity\Client;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
     * 
     * @SWG\Tag(name="Register and Log in")
     * @SWG\Response(
     *     response=200,
     *     description="Creates a new client",
     *     @SWG\Schema(
     *         type="array",
     *         example={"username": "userN", "password": "pass"},
     *         @SWG\Items(ref=@Model(type=Client::class, groups={"full"}))
     *     )
     * )
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $values = json_decode($request->getContent());
        if(isset($values->username, $values->password)) {
            $client = new Client();
            $client->setUsername($values->username);
            $client->setPassword($passwordEncoder->encodePassword($client, $values->password));
            $client->setRoles(["ROLE_CLIENT"]);
            $errors = $validator->validate($client);
            if(count($errors)) {
                $errors = $serializer->serialize($errors, 'json');
                return new Response($errors, 500, [
                    'Content-Type' => 'application/json'
                ]);
            }
            $entityManager->persist($client);
            $entityManager->flush();

            $data = [
                'status' => 201,
                'message' => 'L\'utilisateur a été créé'
            ];

            return new JsonResponse($data, 201);
        }
        $data = [
            'status' => 500,
            'message' => 'Vous devez renseigner les clés username et password'
        ];
        return new JsonResponse($data, 500);
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     * @SWG\Tag(name="Register and Log in")
     * @SWG\Response(
     *     response=200,
     *     description="Get authentication token",
     *     @SWG\Schema(
     *         type="array",
     *         example={"username": "user1", "password": "pass"},
     *         @SWG\Items(ref=@Model(type=Client::class, groups={"full"}))
     *     )
     * )
     */
    public function login(Request $request)
    {
        $client = $this->getUser();

        return $this->json([
            'username' => $client->getUsername(),
            'roles' => $client->getRoles()
        ]);
    }

}