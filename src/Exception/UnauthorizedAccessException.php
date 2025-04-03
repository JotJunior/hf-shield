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

class UnauthorizedAccessException extends RuntimeException
{
    public function __construct()
    {
        $this->message = __('hf-shield.unauthorized_access');
        $this->code = 401;

        parent::__construct($this->message, $this->code);
    }
}
