<?php

namespace App\Controller;

use App\Entity\Phone;
use Swagger\Annotations as SWG;
use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;



/**
 * @Route("/api")
 */

class PhoneController extends AbstractController
{
    /**
     * @Route("/phones/{id}", name="show_phone", methods={"GET"})
     * 
     * @SWG\Tag(name="Phone")
     * @SWG\Response(
     *     response=200,
     *     description="Returns the informations of a phone",
     *     @SWG\Schema(
     *         type="array",
     *         example={},
     *         @SWG\Items(ref=@Model(type=Phone::class, groups={"full"}))
     *     )
     * )
     * 
     */
    public function show(Phone $phone, PhoneRepository $phoneRepository, SerializerInterface $serializer)
    {
        $phone = $phoneRepository->find($phone->getId());
        $data = $serializer->serialize($phone, 'json', [
            'groups' => ['show']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }


     /**
     * @Route("/phones/{page<\d+>?1}", name="list_phone", methods={"GET"})
     * 
     * @SWG\Tag(name="Phone")
     * @SWG\Response(
     *     response=200,
     *     description="Returns the list of phones",
     *     @SWG\Schema(
     *         type="array",
     *         example={},
     *         @SWG\Items(ref=@Model(type=Phone::class, groups={"full"}))
     *     )
     * )
     * 
     */
    public function index(Request $request,PhoneRepository $phoneRepository, SerializerInterface $serializer)
    {
        $page = $request->query->get('page');
        if(is_null($page) || $page < 1) {
            $page = 1;
        }
        $limit = 10;
        $phones = $phoneRepository->findAllPhones($page, $limit);

        $data = $serializer->serialize($phones, 'json', [
            'groups' => ['list']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }



    /**
     * @Route("/phones", name="add_phone", methods={"POST"})
     * 
     * @SWG\Tag(name="Phone")
     * @SWG\Response(
     *     response=200,
     *     description="Post a new phone (ADMIN ONLY)",
     *     @SWG\Schema(
     *         type="array",
     *         example={"name": "new phone", "price": "1000", "description": "phone description"},
     *         @SWG\Items(ref=@Model(type=Phone::class, groups={"full"}))
     *     )
     * )
     * 
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $phone = $serializer->deserialize($request->getContent(), Phone::class, 'json');
        $errors = $validator->validate($phone);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }
        $entityManager->persist($phone);
        $entityManager->flush();
        $data = [
            'status' => 201,
            'message' => 'Le téléphone a bien été ajouté'
        ];
        return new JsonResponse($data, 201);
    }


}
