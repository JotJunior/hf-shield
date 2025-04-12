<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Helper;

use Hyperf\Cache\AnnotationManager;

class CacheAnnotationManagerWrapper extends AnnotationManager
{
    protected function getFormattedKey(string $prefix, array $arguments, ?string $value = null): string
    {
        return CacheStringHelperWrapper::format($prefix, $arguments, $value);
    }
}
