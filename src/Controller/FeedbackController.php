<?php

namespace App\Controller;

use App\DTO\FeedbackDTO;
use App\Entity\Activity;
use App\Entity\User;
use App\Repository\FeedbackRepository;
use App\Repository\UserRepository;
use App\Serializer\ValidationErrorSerializer;
use App\Transformer\FeedbackTransformer;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Feedback controller.
 * @Route("/api/feedback", name="feedback")
 */
class FeedbackController extends AbstractController
{
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var FeedbackTransformer
     */
    private $feedbackTransformer;
    /**
     * @var FeedbackRepository
     */
    private $feedbackRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        FeedbackTransformer $feedbackTransformer,
        FeedbackRepository $feedbackRepository,
        UserRepository $userRepository
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->feedbackTransformer = $feedbackTransformer;
        $this->feedbackRepository = $feedbackRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Add feedback.
     * @Rest\Post("/{activityId}/{userId}", requirements={"activityId"="\d+", "userId"="\d+"})
     * @ParamConverter("activity", options={"mapping": {"activityId" : "id"}})
     * @ParamConverter("userTo", options={"mapping": {"userId" : "id"}})
     * @SWG\Post(
     *     tags={"Feedback"},
     *     summary="Add feedback.",
     *     description="Add feedback.",
     *     operationId="addfeedback",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *     description="ID of Activity on which feedback is given",
     *     in="path",
     *     name="activityId",
     *     required=true,
     *     type="integer",
     * ),
     *     @SWG\Parameter(
     *     description="ID of User to who feedback is given",
     *     in="path",
     *     name="userId",
     *     required=true,
     *     type="integer",
     * ),
     *     @SWG\Parameter(
     *     description="Json body for the request",
     *     name="requestBody",
     *     required=true,
     *     in="body",
     *     @Model(type=FeedbackDTO::class, groups={"AddFeedback"}),
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
     *     @SWG\Property(property="message", type="string", example="Feedback submitted successfully!"),
     *     )
     * )
     * @param Activity $activity
     * @param User $userTo
     * @param Request $request
     * @param ValidationErrorSerializer $validationErrorSerializer
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function addFeedback(
        Activity $activity,
        User $userTo,
        Request $request,
        ValidationErrorSerializer $validationErrorSerializer
    ): JsonResponse {
        $authenticatedUser = $this->getUser();

        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create()->setGroups(array('AddFeedback'));

        $feedbackDTO = $this->serializer->deserialize(
            $data,
            FeedbackDTO::class,
            'json',
            $context
        );

        $errors = $this->validator->validate($feedbackDTO, null, ['ActivityCreate']);

        if (count($errors) > 0) {
            return new JsonResponse(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Bad Request',
                    'errors' => $validationErrorSerializer->serialize($errors)
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $newFeedback = $this->feedbackTransformer->addFeedback($feedbackDTO, $authenticatedUser, $activity, $userTo);
        $this->feedbackRepository->save($newFeedback);

        if ($userTo->getStars() === 0.0) {
            $userTo->setStars($newFeedback->getStars());
        } else {
            $countFeedback = $this->feedbackRepository->countFeedback($userTo)->getQuery()->getSingleScalarResult();
            $newStars = ($userTo->getStars() + $newFeedback->getStars()) / $countFeedback;
            $userTo->setStars($newStars);
        }
        $this->userRepository->save($userTo);


        return new JsonResponse(['message' => 'Feedback submitted successfully!'], Response::HTTP_OK);
    }
}
