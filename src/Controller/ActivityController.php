<?php

namespace App\Controller;

use App\DTO\ActivityDTO;
use App\Entity\Activity;
use App\Exceptions\EntityNotFound;
use App\Service\ActivityTransformer;
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
     * @return Response
     * @SWG\Get(
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
    public function getActivityDetails($id): Response
    {
        $activity = $this->getDoctrine()->getRepository(Activity::class)->find($id);

        if (!$activity) {
            return new JsonResponse(['message' => 'The activity was not found!'], Response::HTTP_NOT_FOUND);
        }

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
     *     response=404,
     *     description="Activity not found.",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="The activity was not found!"),
     * )
     * )
     * @param int $id
     * @param ActivityRepository $activityRepository
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteActivity($id, ActivityRepository $activityRepository): JsonResponse
    {
        $activity = $activityRepository->find($id);

        if (!$activity) {
            return new JsonResponse(['message' => 'The activity was not found!'], Response::HTTP_NOT_FOUND);
        }

        $activityRepository->delete($activity);

        return new JsonResponse(['message' => 'The activity was successfully deleted!'], Response::HTTP_OK);
    }

    /**
     * Create an Activity.
     * @Rest\Post("/create")
     * @SWG\Post(
     *     summary="Create an Activity.",
     *     description="Create an Activity.",
     *     operationId="createActivity",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="Json body for the request",
     *     name="requestBody",
     *     required=true,
     *     in="body",
     *     @Model(type=Activity::class, groups={"ActivityCreate"}),
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
        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create();

        $activityDTO = $this->serializer->deserialize(
            $data,
            ActivityDTO::class,
            'json',
            $context
        );

        $errors = $this->validator->validate($activityDTO);

        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            return new Response($errorsString);
        }

        try {
            $newActivity = $this->transformer->transform($activityDTO);
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
     *     @Model(type=Activity::class, groups={"ActivityEdit"}),
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
     *     response=404,
     *     description="Technoology not found.",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="No technology found."),
     *     @SWG\Property(property="entity", type="integer", example="App\\Entity\\Technology"),
     *     @SWG\Property(property="id", type="integer", example=999),
     * )
     * )
     * @param $id
     * @param ActivityRepository $activityRepository
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction($id, ActivityRepository $activityRepository, Request $request): Response
    {
        /** Verifying if the activity exists*/
        $activity = $activityRepository->find($id);

        if (!$activity) {
            return new JsonResponse(['message' => 'The activity was not found!'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create();

        $activityDTO = $this->serializer->deserialize(
            $data,
            ActivityDTO::class,
            'json',
            $context
        );

        $errors = $this->validator->validate($activityDTO);

        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            return new JsonResponse(['message' => $errorsString], Response::HTTP_BAD_REQUEST);
        }

        try {
            $activityToEdit = $this->transformer->transform($activityDTO);
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
}
