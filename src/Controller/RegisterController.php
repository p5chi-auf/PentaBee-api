<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Exceptions\EntityNotFound;
use App\Repository\UserRepository;
use App\Service\UserTransformer;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Register controller.
 * @Route("/api", name="register")
 */
class RegisterController extends AbstractController
{
    /**
     * @var UserTransformer
     */
    private $transformer;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        UserTransformer $transformer,
        ValidatorInterface $validator
    ) {
        $this->transformer = $transformer;
        $this->validator = $validator;
    }

    /**
     * Create an User.
     * @Rest\Post("/register")
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param UserRepository $userRepository
     * @return JsonResponse|Response
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EntityNotFound
     */
    public function createUser(SerializerInterface $serializer, Request $request, UserRepository $userRepository)
    {
        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create()->setGroups(array('UserCreate'));

        $userDTO = $serializer->deserialize(
            $data,
            UserDTO::class,
            'json',
            $context
        );

        $errors = $this->validator->validate($userDTO, null, ['UserCreate']);

        if (count($errors) > 0) {
            $errorsString = (string)$errors;

            return new Response($errorsString);
        }

        $newUser = $this->transformer->registerTransform($userDTO);

        $userRepository->save($newUser);
        return new JsonResponse(['message' => 'User successfully created!'], Response::HTTP_OK);
    }
}
