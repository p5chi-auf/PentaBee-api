<?php

namespace App\Controller;

use App\DTO\ActivityDTO;
use App\Entity\Activity;
use App\Entity\User;
use App\Exceptions\EntityNotFound;
use App\Repository\ActivityUserRepository;
use App\Service\ActivityTransformer;
use DateTime;
use JMS\Serializer\DeserializationContext;
use App\Repository\ActivityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Swagger\Annotations as SWG;

/**
 * Activity controller.
 * @Route("/api/activities", name="activities")
 */
class ActivityController extends AbstractController
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var ActivityTransformer */
    private $transformer;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        SerializerInterface $serializer,
        ActivityTransformer $transformer,
        ValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->transformer = $transformer;
        $this->validator = $validator;
    }

    /**
     * Get a list of all activities
     * @Rest\Get("/")
     * @return Response
     * @SWG\Get(
     *     tags={"Activity"},
     *     summary="Get a list of all activities",
     *     description="Get a list of all activities",
     *     operationId="getActivities",
     *     produces={"application/json"},
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Successfull operation!",
     *     @SWG\Schema(
     *     type="array",
     *     @Model(type=Activity::class, groups={"ActivityList"}),
     * )
     * )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized.",
     *     @SWG\Schema(
     *     @SWG\Property(property="code", type="integer", example=401),
     *     @SWG\Property(property="message", type="string", example="JWT Token not found"),
     *     )
     * )
     */
    public function getActivitiesList(): Response
    {
        $activities = $this->getDoctrine()->getRepository(Activity::class)->findAll();

        /** @var SerializationContext $context */
        $context = SerializationContext::create()->setGroups(array('ActivityList'));

        $json = $this->serializer->serialize(
            $activities,
            'json',
            $context
        );

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * Get details about and Activity
     * @Rest\Get("/{id}")
     * @param Activity $activity
     * @return Response
     * @SWG\Get(
     *     tags={"Activity"},
     *     summary="Get details about and Activity",
     *     description="Get details about and Activity",
     *     operationId="getActivitById",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="ID of Activity to return",
     *     in="path",
     *     name="id",
     *     required=true,
     *     type="integer",
     * )
     * )
     * @SWG\Response(
     *     response="200",
     *     description="Successfull operation!",
     *     @Model(type=Activity::class, groups={"ActivityDetails"}),
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized.",
     *     @SWG\Schema(
     *     @SWG\Property(property="code", type="integer", example=401),
     *     @SWG\Property(property="message", type="string", example="JWT Token not found"),
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Activity not found.",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="The activity was not found!"),
     * )
     * )
     */
    public function getActivityDetails(Activity $activity): Response
    {
        /** @var SerializationContext $context */
        $context = SerializationContext::create()->setGroups(array('ActivityDetails'));

        $json = $this->serializer->serialize(
            $activity,
            'json',
            $context
        );

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * Delete an Activity.
     * @Rest\Delete("/{id}/delete")
     * @SWG\Delete(
     *     tags={"Activity"},
     *     summary="Delete an Activity.",
     *     description="Delete an Activity.",
     *     operationId="deleteActivityById",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="ID of Activity to delete",
     *     in="path",
     *     name="id",
     *     required=true,
     *     type="integer",
     * )
     * )
     * @SWG\Response(
     *     response="200",
     *     description="Successfull operation!",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="The activity was successfully deleted!"),
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized.",
     *     @SWG\Schema(
     *     @SWG\Property(property="code", type="integer", example=401),
     *     @SWG\Property(property="message", type="string", example="JWT Token not found"),
     *     )
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Forbidden",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="Access denied!"),
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Activity not found.",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="The activity was not found!"),
     * )
     * )
     * @param Activity $activity
     * @param ActivityRepository $activityRepository
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteActivity(Activity $activity, ActivityRepository $activityRepository): JsonResponse
    {
        $authenticatedUser = $this->getUser();

        if ($authenticatedUser !== $activity->getOwner()) {
            return new JsonResponse(['message' => 'Access denied!'], Response::HTTP_FORBIDDEN);
        }

        $activityRepository->delete($activity);

        return new JsonResponse(['message' => 'The activity was successfully deleted!'], Response::HTTP_OK);
    }

    /**
     * Create an Activity.
     * @Rest\Post("/create")
     * @SWG\Post(
     *     tags={"Activity"},
     *     summary="Create an Activity.",
     *     description="Create an Activity.",
     *     operationId="createActivity",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="Json body for the request",
     *     name="requestBody",
     *     required=true,
     *     in="body",
     *     @Model(type=ActivityDTO::class, groups={"ActivityCreate"}),
     * )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized.",
     *     @SWG\Schema(
     *     @SWG\Property(property="code", type="integer", example=401),
     *     @SWG\Property(property="message", type="string", example="JWT Token not found"),
     *     )
     * )
     * @SWG\Response(
     *     response="201",
     *     description="Successfull operation!",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="Activity successfully created!"),
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Activity not found.",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="No technology found."),
     *     @SWG\Property(property="entity", type="integer", example="App\\Entity\\Technology"),
     *     @SWG\Property(property="id", type="integer", example=999),
     * )
     * )
     * @param ActivityRepository $activityRepository
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(ActivityRepository $activityRepository, Request $request): Response
    {
        $owner = $this->getUser();

        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create()->setGroups(array('ActivityCreate'));

        $activityDTO = $this->serializer->deserialize(
            $data,
            ActivityDTO::class,
            'json',
            $context
        );

        $errors = $this->validator->validate($activityDTO, null, ['ActivityCreate']);

        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            return new Response($errorsString);
        }

        try {
            $newActivity = $this->transformer->createTransform($activityDTO, $owner);
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

        $activityRepository->save($newActivity);

        return new JsonResponse(['message' => 'Activity successfully created!'], Response::HTTP_CREATED);
    }

    /**
     * Edit an Activity.
     * @Rest\Post("/{id}/edit")
     * @SWG\Post(
     *     tags={"Activity"},
     *     summary="Edit an Activity.",
     *     description="Edit an Activity.",
     *     operationId="editActivity",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="ID of Activity to edit",
     *     in="path",
     *     name="id",
     *     required=true,
     *     type="integer",
     * ),
     *     @SWG\Parameter(
     *     description="Json body for the request",
     *     name="requestBody",
     *     required=true,
     *     in="body",
     *     @Model(type=ActivityDTO::class, groups={"ActivityEdit"}),
     * )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized.",
     *     @SWG\Schema(
     *     @SWG\Property(property="code", type="integer", example=401),
     *     @SWG\Property(property="message", type="string", example="JWT Token not found"),
     *     )
     * )
     * @SWG\Response(
     *     response="201",
     *     description="Successfull operation!",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="Activity successfully edited!"),
     *     )
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Forbidden",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="Access denied!"),
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Technoology not found.",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="No technology found."),
     *     @SWG\Property(property="entity", type="integer", example="App\\Entity\\Technology"),
     *     @SWG\Property(property="id", type="integer", example=999),
     * )
     * )
     * @param Activity $activity
     * @param ActivityRepository $activityRepository
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(Activity $activity, ActivityRepository $activityRepository, Request $request): Response
    {
        $authenticatedUser = $this->getUser();

        if ($authenticatedUser !== $activity->getOwner()) {
            return new JsonResponse(['message' => 'Access denied!'], Response::HTTP_FORBIDDEN);
        }
        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create()->setGroups(array('ActivityEdit'));

        $activityDTO = $this->serializer->deserialize(
            $data,
            ActivityDTO::class,
            'json',
            $context
        );

        $errors = $this->validator->validate($activityDTO, null, ['ActivityEdit']);

        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            return new JsonResponse(['message' => $errorsString], Response::HTTP_BAD_REQUEST);
        }

        try {
            $activityToEdit = $this->transformer->editTransform($activityDTO, $activity);
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
        $activityRepository->save($activityToEdit);

        return new JsonResponse(['message' => 'Activity successfully edited!'], Response::HTTP_OK);
    }

    /**
     * Apply for an Activity.
     * @Rest\Post("/{id}/apply")
     * @SWG\Post(
     *     tags={"Activity"},
     *     summary="Apply for an Activity.",
     *     description="Apply for an Activity.",
     *     operationId="applyForActivity",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="ID of Activity to apply",
     *     in="path",
     *     name="id",
     *     required=true,
     *     type="integer",
     * )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized.",
     *     @SWG\Schema(
     *     @SWG\Property(property="code", type="integer", example=401),
     *     @SWG\Property(property="message", type="string", example="JWT Token not found"),
     *     )
     * )
     * @SWG\Response(
     *     response="200",
     *     description="Successfull operation!",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="Applied with succes!"),
     *     )
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Already applied/ activity finnished/ deadline passed!",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="You already applied!"),
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Activity not found.",
     * )
     * @param Activity $activity
     * @param ActivityUserRepository $activityUserRepo
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function applyForActivity(Activity $activity, ActivityUserRepository $activityUserRepo): JsonResponse
    {
        $applierUser = $this->getUser();

        if ($activityUserRepo->hasUserApplied($applierUser, $activity)) {
            return new JsonResponse(['message' => 'You already applied!'], Response::HTTP_BAD_REQUEST);
        }

        if ($activity->getOwner() === $this->getUser()) {
            return new JsonResponse(['message' => 'You are the owner of this Job!'], Response::HTTP_BAD_REQUEST);
        }

        if ($activity->getStatus() !== Activity::STATUS_NEW
            || $activity->getApplicationDeadline() < new DateTime('now')
        ) {
            return new JsonResponse(
                ['message' => 'Activity is already finished or application deadline passed!'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $activityUserRepo->apply($activity, $applierUser);
        return new JsonResponse(['message' => 'Applied with success!'], Response::HTTP_OK);
    }

    /**
     *Get a list of all applicants.
     * @Rest\Get("/{id}/applicants")
     * @SWG\Get(
     *     tags={"Activity"},
     *     summary="Get a list of all applicants",
     *     description="Get a list of all applicants",
     *     operationId="getApplicants",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="ID of Activity",
     *     in="path",
     *     name="id",
     *     required=true,
     *     type="integer",
     * )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Successfull operation!",
     *     @SWG\Schema(
     *     type="array",
     *     @Model(type=User::class, groups={"UserList"}),
     * )
     * )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized.",
     *     @SWG\Schema(
     *     @SWG\Property(property="code", type="integer", example=401),
     *     @SWG\Property(property="message", type="string", example="JWT Token not found"),
     *     )
     * )
     * @param Activity $activity
     * @param ActivityUserRepository $activityUserRepo
     * @return JsonResponse
     */
    public function listOfApplicants(Activity $activity, ActivityUserRepository $activityUserRepo): JsonResponse
    {
        $applicants = $activityUserRepo->getApplicantsForActivity($activity);

        /** @var SerializationContext $context */
        $context = SerializationContext::create()->setGroups(array('UserList'));

        $json = $this->serializer->serialize(
            $applicants,
            'json',
            $context
        );

        return new JsonResponse($json, 200, [], true);
    }
}
