<?php

namespace Jot\HfOAuth2\Exception;

class MissingResourceScopeException extends \RuntimeException
{
    private const MESSAGE = 'No authorization scopes have been registered for this resource. Please verify your configuration.';

    protected $message = self::MESSAGE;
    protected $code = 401;
}