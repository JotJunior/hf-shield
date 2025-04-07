<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield;

use Jot\HfShield\Annotation\Scope;

class AllowedScopes
{
    private static array $targets = [];

    public static function addTarget(string $target, string $method, ?object $scope): void
    {
        self::$targets[$target][$method] = $scope;
    }

    public static function list(string $target): array
    {
        return self::$targets[$target] ?? [];
    }

    public static function get(string $target, string $method): ?Scope
    {
        return self::$targets[$target][$method] ?? null;
    }
}
