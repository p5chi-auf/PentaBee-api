<?php

namespace App\Filters;

class AssignedUsersListSort extends BaseSorting
{
    /** @var integer $seniority */
    public $seniority;

    public function setSortingFields(array $sorting): self
    {
        if (!empty($sorting['seniority']) && $this->isSortingParamValid($sorting['seniority'])) {
            $this->seniority = $sorting['seniority'];
        }

        return $this;
    }
}
