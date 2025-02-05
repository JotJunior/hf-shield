<?php

namespace Jot\HfOAuth2\Exception;

class UnauthorizedAccessException extends \RuntimeException
{
    protected $message = 'Unauthorized access';
    protected $code = 401;
}