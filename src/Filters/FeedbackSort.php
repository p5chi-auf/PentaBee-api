<?php

namespace App\Filters;

use DateTime;

class FeedbackSort
{
    /**
     * @var DateTime
     */
    public $createdAt = 'desc';

    /**
     * @var integer
     */
    public $stars;

    private function isSortingParamValid($sortingParameter): bool
    {
        strtolower($sortingParameter);
        return $sortingParameter === 'desc' || $sortingParameter === 'asc';
    }

    public function setSortingFields(array $sorting): self
    {
        if (!empty($sorting['stars']) && $this->isSortingParamValid($sorting['stars'])) {
            $this->stars = $sorting['stars'];
        }
        if (!empty($sorting['createdAt']) && $this->isSortingParamValid($sorting['createdAt'])) {
            $this->createdAt = $sorting['createdAt'];
        }

        return $this;
    }
}
