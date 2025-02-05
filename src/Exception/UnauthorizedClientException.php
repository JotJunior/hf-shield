<?php

namespace Jot\HfShield\Exception;

class UnauthorizedClientException extends \RuntimeException
{
    protected $message = 'Unauthorized Client';
    protected $code = 401;
}