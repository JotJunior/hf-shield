<?php

namespace Jot\HfOAuth2;

use Jot\HfOAuth2\Annotation\Scope;

class AllowedScopes
{

    private static array $targets = [];

    static public function addTarget(string $target, string $method, ?object $scope): void
    {
        self::$targets[$target][$method] = $scope;
    }

    static public function list(string $target): array
    {
        return self::$targets[$target] ?? [];
    }

    static public function get(string $target, string $method): ?Scope
    {
        return self::$targets[$target][$method] ?? null;
    }

}