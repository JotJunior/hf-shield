<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\AuthOption\Webauthn\Exception;

use Jot\HfShield\Exception\AbstractException;

use function Hyperf\Translation\__;

class InvalidPublicKeyCredentialException extends AbstractException
{
    public function __construct(array $metadata = [])
    {
        $this->metadata = $metadata;
        $this->message = __('hf-shield.invalid_public_key_credential');
        $this->code = 401;
        parent::__construct($this->message, $this->code);
    }
}
