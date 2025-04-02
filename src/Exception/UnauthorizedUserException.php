<?php

namespace Jot\HfShield\Exception;

use function Hyperf\Translation\__;

class UnauthorizedUserException extends \RuntimeException
{
    public function __construct()
    {
        $this->message = __('hf-shield.unauthorized_user');
        $this->code = 401;
        parent::__construct($this->message, $this->code);
    }
}