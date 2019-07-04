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
        if ($sorting) {
            if (array_key_exists('name', $sorting)) {
                $this->name = $sorting['name'];
            }
            if (array_key_exists('createdAt', $sorting)) {
                $this->createdAt = $sorting['createdAt'];
            }
            if (array_key_exists('finalDeadline', $sorting)) {
                $this->finalDeadline = $sorting['finalDeadline'];
            }
        }

        return $this;
    }
}
