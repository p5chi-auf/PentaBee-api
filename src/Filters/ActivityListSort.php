<?php

namespace App\Filters;

use DateTime;

class ActivityListSort
{
    /** @var DateTime */
    public $createdAt;

    /** @var DateTime */
    public $finalDeadline;

    /** @var string */
    public $name;

    public function setSortingFields($sorting)
    {
        if (!empty($sorting['name'])) {
            $this->name = $sorting['name'];
        }
        if (!empty($sorting['createdAt'])) {
            $this->createdAt = $sorting['createdAt'];
        }
        if (!empty($sorting['finalDeadline'])) {
            $this->finalDeadline = $sorting['finalDeadline'];
        }

        return $this;
    }
}
