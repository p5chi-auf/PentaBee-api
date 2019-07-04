<?php

namespace App\Filters;

class ActivityListPagination
{
    /**
     * @var integer $currentPage
     */
    public $currentPage;

    /**
     * @var integer $pageSize
     */
    public $pageSize;

    public function setPaginationFields(array $pagination): self
    {
        if (!empty($pagination['page']) && filter_var($pagination['page'], FILTER_VALIDATE_INT)) {
            $this->currentPage = (integer)$pagination['page'];
        } else {
            $this->currentPage = 1;
        }
        if (!empty($pagination['per_page']) && filter_var($pagination['per_page'], FILTER_VALIDATE_INT)) {
            $this->pageSize = (integer)$pagination['per_page'];
        } else {
            $this->pageSize = 10;
        }

        return $this;
    }
}
