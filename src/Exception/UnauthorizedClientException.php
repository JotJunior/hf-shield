<?php

namespace Jot\HfShield\Exception;

class UnauthorizedClientException extends \RuntimeException
{
    public function __construct()
    {
        $this->message = __('hf-shield.unauthorized_client');
        $this->code = 401;
    }
}