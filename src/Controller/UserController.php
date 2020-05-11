<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use Swagger\Annotations as SWG;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
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

class UserController extends AbstractController
{
     /**
     * @Route("/users/{page<\d+>?1}", name="list_user", methods={"GET"})
     * 
     * @SWG\Tag(name="User")
     * @SWG\Response(
     *     response=200,
     *     description="Returns the user list (ADMIN ONLY)",
     *     @SWG\Schema(
     *         type="array",
     *         example={},
     *         @SWG\Items(ref=@Model(type=User::class, groups={"full"}))
     *     )
     * )
     * 
     * 
     * @IsGranted("ROLE_CLIENT")
     */
    public function index(Request $request,UserRepository $userRepository, SerializerInterface $serializer)
    {
        $page = $request->query->get('page');
        if(is_null($page) || $page < 1) {
            $page = 1;
        }
        $limit = 10;
        $users = $userRepository->findAllUsers($page, $limit);

        $data = $serializer->serialize($users, 'json', [
            'groups' => ['list']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }


     /**
     * @Route("/users/{id}", name="show_user", methods={"GET"})
     * @SWG\Tag(name="User")
     * @SWG\Response(
     *     response=200,
     *     description="Returns the informations of an user (ADMIN ONLY)",
     *     @SWG\Schema(
     *         type="array",
     *         example={},
     *         @SWG\Items(ref=@Model(type=User::class, groups={"full"}))
     *     )
     * ) 
     * 
     * @IsGranted("ROLE_CLIENT")
     */
    public function show(User $user, UserRepository $userRepository,SerializerInterface $serializer)
    {
        $user = $userRepository->find($user->getId());
        $data = $serializer->serialize($user, 'json', [
            'groups' => ['show']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

       /**
     * @Route("/user", name="add_user", methods={"POST"})
     * @SWG\Tag(name="User")
     * @SWG\Response(
     *     response=200,
     *     description="Add a new user",
     *     @SWG\Schema(
     *         type="array",
     *         example={"first_name": "fname", "last_name": "lname", "email": "example@email.com"},
     *         @SWG\Items(ref=@Model(type=User::class, groups={"full"}))
     *     )
     * )
     * 
     * @IsGranted("ROLE_CLIENT")
     */
    public function add(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $validator->validate($user);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

        dd($user->setClient($user->getClient()));
        $entityManager->persist($user);
        $entityManager->flush();
        $data = [
            'status' => 201,
            'message' => 'User has been added successfully, see it at (GET) /api/user/' . $user->getId()
        ];

        return new JsonResponse($data, 201);
    }


    /**
     * @Route("/users/{id}", name="update_user", methods={"PUT"})
     * 
     * @SWG\Tag(name="User")
     * @SWG\Response(
     *     response=200,
     *     description="Edit an existing user (ADMIN ONLY)",
     *     @SWG\Schema(
     *         type="array",
     *         example={"username": "user2"},
     *         @SWG\Items(ref=@Model(type=User::class, groups={"full"}))
     *     )
     * )
     * 
     * @IsGranted("ROLE_CLIENT")
     */
    public function update(Request $request, SerializerInterface $serializer, User $user, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $userUpdate = $entityManager->getRepository(User::class)->find($user->getId());
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
            'message' => 'L\'utilisateur  a bien été mis à jour'
        ];
        return new JsonResponse($data);
    }

     /**
     * @Route("/users/{id}", name="delete_user", methods={"DELETE"})
     * 
     * @SWG\Tag(name="User")
     * @SWG\Response(
     *     response=204,
     *     description="Delete an existing user (ADMIN ONLY)",
     *     @SWG\Schema(
     *         type="array",
     *         example={},
     *         @SWG\Items(ref=@Model(type=User::class, groups={"full"}))
     *     )
     * )
     * 
     * @IsGranted("ROLE_CLIENT")
     */
    public function delete(User $user, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($user);
        $entityManager->flush();
        return new Response(null, 204);
    }
}
