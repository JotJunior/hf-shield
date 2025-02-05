<?php

namespace Jot\HfShield\Exception;

class UnauthorizedAccessException extends \RuntimeException
{
    protected $message = 'Unauthorized access';
    protected $code = 401;
}