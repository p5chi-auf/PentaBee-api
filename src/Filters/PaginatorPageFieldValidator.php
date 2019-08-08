<?php

namespace App\Filters;

class PaginatorPageFieldValidator
{
    public function isParameterValid($maxPage, $numPage): bool
    {
        return !(($maxPage !== 0 && $numPage > $maxPage) || $numPage < 0);
    }
}
