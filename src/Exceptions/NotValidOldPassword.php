<?php

namespace App\Exceptions;

class NotValidOldPassword extends \Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
