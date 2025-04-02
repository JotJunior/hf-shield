<?php

namespace Jot\HfShield\Exception;

use function Hyperf\Translation\__;

class MissingResourceScopeException extends \RuntimeException
{
    public function __construct()
    {
        $this->message = __('hf-shield.missing_resource_scope');
        $this->code = 401;
        parent::__construct($this->message, $this->code);
    }
}
