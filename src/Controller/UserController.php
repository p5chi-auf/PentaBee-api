<?php

namespace App\Controller;

use App\DTO\UserChangePasswordDTO;
use App\DTO\UserEditDTO;
use App\Entity\User;
use App\Exceptions\EntityNotFound;
use App\Exceptions\NotValidOldPassword;
use App\Repository\UserRepository;
use App\Service\UserTransformer;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * User controller.
 * @Route("/api/user", name="user")
 */
class UserController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var UserTransformer
     */
    private $transformer;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        SerializerInterface $serializer,
        UserTransformer $transformer,
        ValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->transformer = $transformer;
        $this->validator = $validator;
    }

    /**
     * Show details about an User.
     * @Rest\Get("/{id}")
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

    /**
     * Modify an User.
     * @Rest\Post("/{id}/edit")
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param UserRepository $userRepository
     * @return JsonResponse|Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editUser($id, Request $request, UserRepository $userRepository)
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['message' => 'The user was not found!'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create();

        $userEditDTO = $this->serializer->deserialize(
            $data,
            UserEditDTO::class,
            'json',
            $context
        );

        $errors = $this->validator->validate($userEditDTO);

        if (count($errors) > 0) {
            $errorsString = (string)$errors;

            return new JsonResponse(['message' => $errorsString], Response::HTTP_BAD_REQUEST);
        }


        try {
            $userEdit = $this->transformer->editTransform($userEditDTO);
        } catch (EntityNotFound $exception) {
            return new JsonResponse(
                [
                    'message' => $exception->getMessage(),
                    'entity' => $exception->getEntity(),
                    'id' => $exception->getId()
                ],
                Response::HTTP_NOT_FOUND
            );
        }


        $userRepository->save($userEdit);
        return new JsonResponse(['message' => 'User successfully edited!'], Response::HTTP_OK);
    }

    /**
     * Delete an User.
     * @Rest\Delete("/{id}/delete")
     * @param int $id
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteUser($id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['message' => 'The user was not found!'], Response::HTTP_NOT_FOUND);
        }

        $userRepository->delete($user);

        return new JsonResponse(['message' => 'The user was successfully deleted!'], Response::HTTP_OK);
    }

    /**
     * Change password
     * @Rest\Post("/{id}/change_password")
     * @param int $id
     * @param UserRepository $userRepository
     * @param Request $request
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function userChangePassword($id, UserRepository $userRepository, Request $request): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['message' => 'The user was not found!'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create();

        $userChangePassDTO = $this->serializer->deserialize(
            $data,
            UserChangePasswordDTO::class,
            'json',
            $context
        );

        $errors = $this->validator->validate($userChangePassDTO);

        if (count($errors) > 0) {
            $errorsString = (string)$errors;

            return new JsonResponse(['message' => $errorsString], Response::HTTP_BAD_REQUEST);
        }


        try {
            $userChangePassword = $this->transformer->changePasswordTransform($userChangePassDTO);
        } catch (NotValidOldPassword $exception) {
            return new JsonResponse(
                [
                    'message' => $exception->getMessage()
                ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (EntityNotFound $exception) {
            return new JsonResponse(
                [
                    'message' => $exception->getMessage(),
                    'entity' => $exception->getEntity(),
                    'id' => $exception->getId()
                ],
                Response::HTTP_NOT_FOUND
            );
        }


        $userRepository->save($userChangePassword);
        return new JsonResponse(['message' => 'Password successfully changed!'], Response::HTTP_OK);
    }
}
