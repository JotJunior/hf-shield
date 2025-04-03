<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
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
