<?php

namespace App\Filters;

class ApplicantsListSort
{
    /** @var integer $seniority */
    public $seniority;

    private function isSortingParamValid($sortingParameter): bool
    {
        strtolower($sortingParameter);
        return $sortingParameter === 'desc' || $sortingParameter === 'asc';
    }

    public function setSortingFields(array $sorting): self
    {
        if (!empty($sorting['seniority']) && $this->isSortingParamValid($sorting['seniority'])) {
            $this->seniority = $sorting['seniority'];
        }

        return $this;
    }
}
