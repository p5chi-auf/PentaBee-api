<?php

namespace App\Transformer;

use App\DTO\FeedbackDTO;
use App\Entity\Activity;
use App\Entity\Feedback;
use App\Entity\User;

class FeedbackTransformer
{
    public function addFeedback(
        FeedbackDTO $dto,
        User $authenticatedUser,
        Activity $activity,
        User $userTo
    ) {
        $feedback = new Feedback();
        $feedback->setStars($dto->stars);
        $feedback->setComment($dto->comment);
        $feedback->setUserFrom($authenticatedUser);
        $feedback->setUserTo($userTo);
        $feedback->setActivity($activity);

        return $feedback;
    }

}