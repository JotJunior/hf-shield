<?php

namespace Jot\HfShield\Exception;

class UnauthorizedSessionException extends \RuntimeException
{
    public function __construct()
    {
        $this->message = __('hf-shield.unauthorized_session');
        $this->code = 401;
    }
}