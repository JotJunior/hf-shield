<?php

namespace Jot\HfShield\Exception;

class MissingResourceScopeException extends \RuntimeException
{
    public function __construct()
    {
        $this->message = __('hf-shield.missing_resource_scope');
        $this->code = 401;
    }
}