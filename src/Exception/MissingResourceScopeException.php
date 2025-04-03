<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
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
