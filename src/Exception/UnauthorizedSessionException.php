<?php

namespace Jot\HfShield\Exception;

class UnauthorizedSessionException extends \RuntimeException
{
    protected $message = 'Unauthorized access. Please check your session configuration.';
    protected $code = 401;
}