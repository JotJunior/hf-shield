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

use function Hyperf\Translation\__;

class InvalidOtpCodeException extends AbstractException
{
    public function __construct(string $message, array $metadata = [])
    {
        $this->metadata = $metadata;
        $this->message = __($message);
        $this->code = 400;
        parent::__construct($this->message, $this->code);
    }
}
