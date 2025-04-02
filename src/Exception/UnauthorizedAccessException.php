<?php

namespace Jot\HfShield\Exception;

class UnauthorizedAccessException extends \RuntimeException
{
    public function __construct()
    {
        $this->message = __('hf-shield.unauthorized_access');
        $this->code = 401;
    }
}