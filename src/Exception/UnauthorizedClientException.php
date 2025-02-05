<?php

namespace Jot\HfOAuth2\Exception;

class UnauthorizedClientException extends \RuntimeException
{
    protected $message = 'Unauthorized Client';
    protected $code = 401;
}