<?php

namespace App\Controller;

use App\DTO\ActivityDTO;
use App\Entity\Activity;
use App\Entity\ActivityUser;
use App\Entity\User;
use App\Exceptions\EntityNotFound;
use App\Handlers\ActivityHandler;
use App\Handlers\ActivityUserHandler;
use App\Mailer\Mailer;
use App\Repository\ActivityUserRepository;
use App\Security\AccessRightsPolicy;
use App\Serializer\ValidationErrorSerializer;
use App\Service\ActivityTransformer;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use JMS\Serializer\DeserializationContext;
use App\Repository\ActivityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swift_Mailer;
use Swift_Message;
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
    /**
     * @var AccessRightsPolicy
     */
    private $accessRightsPolicy;
    /**
     * @var ActivityHandler
     */
    private $activityHandler;
    /**
     * @var ActivityUserHandler
     */
    private $activityUserHandler;

    public function __construct(
        SerializerInterface $serializer,
        ActivityTransformer $transformer,
        ValidatorInterface $validator,
        AccessRightsPolicy $accessRightsPolicy,
        ActivityHandler $activityHandler,
        ActivityUserHandler $activityUserHandler
    ) {
        $this->serializer = $serializer;
        $this->transformer = $transformer;
        $this->validator = $validator;
        $this->accessRightsPolicy = $accessRightsPolicy;
        $this->activityHandler = $activityHandler;
        $this->activityUserHandler = $activityUserHandler;
    }

    /**
     * Get a list of all activities
     * @Rest\Get("/all/{page<\d+>}", defaults={"page" = 1})
     * @param $page
     * @return JsonResponse
     * @SWG\Get(
     *     tags={"Activity"},
     *     summary="Get a list of all activities",
     *     description="Get a list of all activities",
     *     operationId="getActivities",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="Number of page",
     *     in="path",
     *     required=false,
     *     name="page",
     *     type="integer",
     *     default="1",
     * )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Successfull operation!",
     *     @SWG\Schema(
     *     @SWG\Property(property="numResults", type="integer"),
     *     @SWG\Property(property="perPage", type="integer"),
     *     @SWG\Property(property="numPages", type="integer"),
     *     @SWG\Property(property="results", type="array", @Model(type=Activity::class, groups={"ActivityList"}),
     *     )
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
    public function getActivitiesList(int $page): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse(
            json_encode($this->activityHandler->getActivitiesListPaginated($user, $page)),
            200,
            [],
            true
        );
    }

    /**
     * Get details about and Activity
     * @Rest\Get("/{id}", requirements={"id"="\d+"})
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
        $user = $this->getUser();
        $rights = $this->accessRightsPolicy->canAccessActivity($activity, $user);

        if ($rights === false) {
            return new JsonResponse(['message' => 'Access denied!'], Response::HTTP_FORBIDDEN);
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
     * @Rest\Delete("/{id}/delete", requirements={"id"="\d+"})
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
     * @param ValidationErrorSerializer $validationErrorSerializer
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createAction(
        ActivityRepository $activityRepository,
        Request $request,
        ValidationErrorSerializer $validationErrorSerializer
    ): Response {
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
            return new JsonResponse(
                ['errors' => $validationErrorSerializer->serialize($errors)],
                Response::HTTP_BAD_REQUEST
            );
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
     * @Rest\Post("/{id}/edit", requirements={"id"="\d+"})
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
     * @param ValidationErrorSerializer $validationErrorSerializer
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(
        Activity $activity,
        ActivityRepository $activityRepository,
        Request $request,
        ValidationErrorSerializer $validationErrorSerializer
    ): Response {
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
            return new JsonResponse(
                ['errors' => $validationErrorSerializer->serialize($errors)],
                Response::HTTP_BAD_REQUEST
            );
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
     * @Rest\Post("/{id}/apply", requirements={"id"="\d+"})
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
     *     description="Already applied!",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="You cannot apply!"),
     *     )
     * )
     * @SWG\Response(
     *     response="403",
     *     description="You are the owner of the Job!",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="Access denied!"),
     *     )
     * )
     * @SWG\Response(
     *     response="406",
     *     description="You are the owner of the Job!",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="You are the owner of this Job!"),
     *     )
     * )
     * @SWG\Response(
     *     response="412",
     *     description="Activity already finished or application deadline passed!",
     *     @SWG\Schema(
     *     @SWG\Property(
     *     property="message",
     *     type="string", example="Activity is already finished or application deadline passed!"),
     *     )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Activity not found.",
     * )
     * @param Activity $activity
     * @param ActivityUserRepository $activityUserRepo
     * @param Swift_Mailer $mailer
     * @return JsonResponse
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function applyForActivity(
        Activity $activity,
        ActivityUserRepository $activityUserRepo,
        Swift_Mailer $mailer
    ): JsonResponse {
        /** @var User $applierUser */
        $applierUser = $this->getUser();

        if ($activity->isPublic() === false) {
            return new JsonResponse(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        if ($activity->getOwner() === $applierUser) {
            return new JsonResponse(['message' => 'You are the owner of this Job!'], Response::HTTP_NOT_ACCEPTABLE);
        }

        if ($activity->getStatus() !== Activity::STATUS_NEW
            || $activity->getApplicationDeadline() < new DateTime('now')
        ) {
            return new JsonResponse(
                ['message' => 'Activity is already finished or application deadline passed!'],
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        $activityUser = $activityUserRepo->getActivityUser($applierUser, $activity);

        if ($activityUser !== null) {
            return new JsonResponse(['message' => 'You cannot apply!'], Response::HTTP_BAD_REQUEST);
        }
        $message = (new Swift_Message(
            'Someone applied your job: ' . $activity->getName()
        ))
            ->setFrom('pentabee.mail@gmail.com')
            ->setTo($activity->getOwner()->getEmail())
            ->setBody(
                $this->renderView(
                    'mail/apply.html.twig',
                    [
                        'owner' => $activity->getOwner(),
                        'user' => $applierUser,
                        'activity' => $activity
                    ]
                ),
                'text/html'
            );

        $mailer->send($message);

        $activityUserRepo->apply($activity, $applierUser);
        return new JsonResponse(['message' => 'Applied with success!'], Response::HTTP_OK);
    }

    /**
     * Invite an User to an Activity.
     * @Rest\Post("/{activityId}/invite/{userId}", requirements={"activityId"="\d+", "userId"="\d+"})
     * @ParamConverter("activity", options={"mapping": {"activityId" : "id"}})
     * @ParamConverter("invitedUser", options={"mapping": {"userId" : "id"}})
     * @SWG\Post(
     *     tags={"Activity"},
     *     summary="Invite user to an Activity.",
     *     description="Invite user to an Activity.",
     *     operationId="inviteToActivity",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="ID of Activity for invite",
     *     in="path",
     *     name="activityId",
     *     required=true,
     *     type="integer",
     * ),
     *     @SWG\Parameter(
     *     description="ID of User to be invited",
     *     in="path",
     *     name="userId",
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
     *     @SWG\Property(property="message", type="string", example="User invited with succes!"),
     *     )
     * )
     * @SWG\Response(
     *     response="400",
     *     description="This user already applied/is invited/is assigned!",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string"),
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
     *     response="406",
     *     description="You are the owner of the Job!",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="You are the owner of this Job!"),
     *     )
     * )
     * @SWG\Response(
     *     response="412",
     *     description="Activity already finished or application deadline passed!",
     *     @SWG\Schema(
     *     @SWG\Property(
     *     property="message",
     *     type="string", example="Activity is already finished or application deadline passed!"),
     *     )
     * )
     * @param Activity $activity
     * @param User $invitedUser
     * @param ActivityUserRepository $activityUserRepo
     * @param Swift_Mailer $mailer
     * @return JsonResponse
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function inviteUserToActivity(
        Activity $activity,
        User $invitedUser,
        ActivityUserRepository $activityUserRepo,
        Swift_Mailer $mailer
    ): JsonResponse {
        /** @var User $authenticatedUser */
        $authenticatedUser = $this->getUser();

        if ($activity->getOwner() !== $authenticatedUser) {
            return new JsonResponse(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        if ($activity->getOwner() === $invitedUser) {
            return new JsonResponse(['message' => 'You are the owner of this Job!'], Response::HTTP_NOT_ACCEPTABLE);
        }

        if ($activity->getStatus() !== Activity::STATUS_NEW
            || $activity->getApplicationDeadline() < new DateTime('now')
        ) {
            return new JsonResponse(
                ['message' => 'Activity is already finished or application deadline passed!'],
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        $activityUser = $activityUserRepo->getActivityUser($invitedUser, $activity);

        if ($activityUser !== null) {
            return new JsonResponse(['message' => 'This user already applied!'], Response::HTTP_BAD_REQUEST);
        }

        $message = (new Swift_Message(
            $authenticatedUser->getName() . ' ' . $authenticatedUser->getSurname() .
            ' invited you for the job: ' . $activity->getName()
        ))
            ->setFrom('pentabee.mail@gmail.com')
            ->setTo($authenticatedUser->getEmail())
            ->setBody(
                $this->renderView(
                    'mail/invite.html.twig',
                    [
                        'owner' => $authenticatedUser,
                        'user' => $invitedUser,
                        'activity' => $activity
                    ]
                ),
                'text/html'
            );

        $mailer->send($message);

        $activityUserRepo->invite($activity, $invitedUser);
        return new JsonResponse(['message' => 'User invited with success!'], Response::HTTP_OK);
    }

    /**
     * Get a list of all applicants.
     * @Rest\Get("/{activityId}/applicants/{page}", defaults={"page" = 1}, requirements={"activityId"="\d+"})
     * @param Activity $activity
     * @param int $page
     * @ParamConverter("activity", options={"mapping": {"activityId" : "id"}})
     * @SWG\Get(
     *     tags={"Activity"},
     *     summary="Get a list of all applicants",
     *     description="Get a list of all applicants",
     *     operationId="getApplicants",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="ID of Activity",
     *     in="path",
     *     name="activityId",
     *     required=true,
     *     type="integer",
     * ),
     *     @SWG\Parameter(
     *     description="Number of page",
     *     in="path",
     *     name="page",
     *     required=false,
     *     type="integer",
     *     default="1",
     * )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Successfull operation!",
     *     @SWG\Schema(
     *     @SWG\Property(property="numResults", type="integer"),
     *     @SWG\Property(property="perPage", type="integer"),
     *     @SWG\Property(property="numPages", type="integer"),
     *     @SWG\Property(property="results", type="array", @Model(type=User::class, groups={"UserList"}),
     *     )
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
     * @return JsonResponse
     */
    public function listOfApplicants(Activity $activity, int $page): JsonResponse
    {
        return new JsonResponse(
            json_encode($this->activityUserHandler->getApplicantsPaginated($activity, $page)),
            200,
            [],
            true
        );
    }

    /**
     * Validate an applicant.
     * @Rest\Post("/{activityId}/applicants/{userId}/accept")
     * @ParamConverter("activity", options={"mapping": {"activityId" : "id"}})
     * @ParamConverter("user", options={"mapping": {"userId" : "id"}})
     * @SWG\Post(
     *     tags={"Activity"},
     *     summary="Validate an applicant.",
     *     description="Validate an applicant.",
     *     operationId="validateApplicant",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="ID of Activity for validation",
     *     in="path",
     *     name="activityId",
     *     required=true,
     *     type="integer",
     * ),
     *     @SWG\Parameter(
     *     description="ID of User to be validated",
     *     in="path",
     *     name="userId",
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
     *     @SWG\Property(property="message", type="string", example="User assigned with success!"),
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
     *     response="400",
     *     description="Bad request.",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="User cannot be assigned!"),
     *     )
     * )
     * @param Activity $activity
     * @param User $user
     * @param ActivityUserRepository $activityUserRepo
     * @return JsonResponse
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function acceptAnUserAppliance(
        Activity $activity,
        User $user,
        ActivityUserRepository $activityUserRepo
    ): JsonResponse {
        $authenticatedUser = $this->getUser();

        if ($activity->getOwner() !== $authenticatedUser) {
            return new JsonResponse(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $activityUser = $activityUserRepo->getActivityUser($user, $activity);
        if ($activityUser === null || $activityUser->getType() !== ActivityUser::TYPE_APPLIED) {
            return new JsonResponse(['message' => 'User cannot be assigned!'], Response::HTTP_BAD_REQUEST);
        }

        $activityUser->setType(ActivityUser::TYPE_ASSIGNED);
        $activityUserRepo->save($activityUser);
        return new JsonResponse(['message' => 'User assigned with success!'], Response::HTTP_OK);
    }

    /**
     * Reject an applicant.
     * @Rest\Post("/{activityId}/applicants/{userId}/decline", requirements={"activityId"="\d+", "userId"="\d+"})
     * @ParamConverter("activity", options={"mapping": {"activityId" : "id"}})
     * @ParamConverter("user", options={"mapping": {"userId" : "id"}})
     * @SWG\Post(
     *     tags={"Activity"},
     *     summary="Reject an applicant.",
     *     description="Reject an applicant.",
     *     operationId="rejectApplicant",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="ID of Activity for rejection",
     *     in="path",
     *     name="activityId",
     *     required=true,
     *     type="integer",
     * ),
     *     @SWG\Parameter(
     *     description="ID of User to be rejected",
     *     in="path",
     *     name="userId",
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
     *     @SWG\Property(property="message", type="string", example="User rejected!"),
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
     *     response="400",
     *     description="Bad request.",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="User cannot be rejected!"),
     *     )
     * )
     * @param Activity $activity
     * @param User $user
     * @param ActivityUserRepository $activityUserRepo
     * @return JsonResponse
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function rejectAnUserAppliance(
        Activity $activity,
        User $user,
        ActivityUserRepository $activityUserRepo
    ): JsonResponse {
        $authenticatedUser = $this->getUser();

        if ($activity->getOwner() !== $authenticatedUser) {
            return new JsonResponse(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $activityUser = $activityUserRepo->getActivityUser($user, $activity);
        if ($activityUser === null || $activityUser->getType() !== ActivityUser::TYPE_APPLIED) {
            return new JsonResponse(['message' => 'User cannot be rejected!'], Response::HTTP_BAD_REQUEST);
        }

        $activityUser->setType(ActivityUser::TYPE_REJECTED);
        $activityUserRepo->save($activityUser);
        return new JsonResponse(['message' => 'User rejected!'], Response::HTTP_OK);
    }

    /**
     * Accept invitation for a job.
     * @Rest\Post("/{id}/accept", requirements={"id"="\d+"})
     * @SWG\Post(
     *     tags={"Activity"},
     *     summary="Accept a invitation for a job.",
     *     description="Accept a invitation for a job.",
     *     operationId="acceptInvitation",
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
     *     @SWG\Property(property="message", type="string", example="You are assigned with success!"),
     *     )
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Bad request",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="You are not invited!"),
     *     )
     * )
     * @param Activity $activity
     * @param ActivityUserRepository $activityUserRepo
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function acceptInvitation(
        Activity $activity,
        ActivityUserRepository $activityUserRepo
    ): JsonResponse {
        $authenticatedUser = $this->getUser();
        $accept = $activityUserRepo->getActivityUser($authenticatedUser, $activity);

        if ($accept === null || $accept->getType() !== ActivityUser::TYPE_INVITED) {
            return new JsonResponse(['message' => 'You are not invited!'], Response::HTTP_BAD_REQUEST);
        }
        $accept->setType(ActivityUser::TYPE_ASSIGNED);
        $activityUserRepo->save($accept);
        return new JsonResponse(['message' => 'You are assigned with success!'], Response::HTTP_OK);
    }

    /**
     * Decline invitation for a job.
     * @Rest\Post("/{id}/decline", requirements={"id"="\d+"})
     * @SWG\Post(
     *     tags={"Activity"},
     *     summary="Decline a invitation for a job.",
     *     description="Decline a invitation for a job.",
     *     operationId="declineInvitation",
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
     *     @SWG\Property(property="message", type="string", example="You declined the invitation!"),
     *     )
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Bad request",
     *     @SWG\Schema(
     *     @SWG\Property(property="message", type="string", example="You are not invited!"),
     *     )
     * )
     * @param Activity $activity
     * @param ActivityUserRepository $activityUserRepo
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function declineInvitation(
        Activity $activity,
        ActivityUserRepository $activityUserRepo
    ): JsonResponse {
        $authenticatedUser = $this->getUser();
        $accept = $activityUserRepo->getActivityUser($authenticatedUser, $activity);

        if ($accept === null || $accept->getType() !== ActivityUser::TYPE_INVITED) {
            return new JsonResponse(['message' => 'You are not invited!'], Response::HTTP_BAD_REQUEST);
        }
        $accept->setType(ActivityUser::TYPE_DECLINED);
        $activityUserRepo->save($accept);
        return new JsonResponse(['message' => 'You declined the invitation!'], Response::HTTP_OK);
    }
}
