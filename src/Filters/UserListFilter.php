<?php

namespace App\Filters;

class UserListFilter
{
    /**
     * @var array $technology
     */
    public $technology;

    public function setFilterFields(array $filter): self
    {
        if (!empty($filter['technology'])) {
            $this->technology = $filter['technology'];
        }
        return $this;
    }
}
