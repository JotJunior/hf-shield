<?php

namespace Jot\HfShield\Exception;

class UnauthorizedUserException extends \RuntimeException
{
    protected $message = 'Unauthorized User';
    protected $code = 401;
}