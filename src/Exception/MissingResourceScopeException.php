<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Exception;

use RuntimeException;

use function Hyperf\Translation\__;

class MissingResourceScopeException extends RuntimeException
{
    public function __construct()
    {
        $this->message = __('hf-shield.missing_resource_scope');
        $this->code = 401;
        parent::__construct($this->message, $this->code);
    }
}
