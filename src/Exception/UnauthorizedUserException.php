<?php

namespace Jot\HfOAuth2\Exception;

class UnauthorizedUserException extends \RuntimeException
{
    protected $message = 'Unauthorized User';
    protected $code = 401;
}