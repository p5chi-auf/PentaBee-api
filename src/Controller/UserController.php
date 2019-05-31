<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Service\UserTransformer;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    /**
     * @var UserTransformer
     */
    private $transformer;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        UserTransformer $transformer,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ) {
        $this->transformer = $transformer;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * Create an User.
     * @Rest\Post("/register")
     *
     * @param SerializerInterface $serializer
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function createAction(SerializerInterface $serializer, Request $request)
    {
        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create();

        $userDTO = $serializer->deserialize(
            $data,
            UserDTO::class,
            'json',
            $context
        );

        $errors = $this->validator->validate($userDTO);

        if (count($errors) > 0) {
            $errorsString = (string)$errors;

            return new Response($errorsString);
        }

        $entityToPersist = $this->transformer->transform($userDTO);

        $em = $this->getDoctrine()->getManager();
        $em->persist($entityToPersist);
        $em->flush();
        return new JsonResponse('Success!', Response::HTTP_CREATED);
    }

    /**
     * Show details about an User.
     * @Rest\Get("/user/{id}")
     * @param User $user
     * @return Response
     */
    public function getUserDetails(User $user): Response
    {

        /** @var SerializationContext $context */
        $context = SerializationContext::create()->setGroups(array('UserDetail'));

        $json = $this->serializer->serialize(
            $user,
            'json',
            $context
        );

        return new JsonResponse($json, 200, [], true);
    }
}

