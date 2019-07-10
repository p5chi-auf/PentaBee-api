<?php

namespace App\Filters;

class UserListSort
{
    /**
     * @var int
     */
    public $seniority = 'desc';

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
