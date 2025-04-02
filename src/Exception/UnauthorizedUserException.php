<?php

namespace Jot\HfShield\Exception;

class UnauthorizedUserException extends \RuntimeException
{
    public function __construct()
    {
        $this->message = __('hf-shield.unauthorized_user');
        $this->code = 401;
    }
}