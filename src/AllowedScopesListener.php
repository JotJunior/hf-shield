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

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Jot\HfShield\Annotation\Scope;
use Psr\Container\ContainerInterface;

#[Listener]
class AllowedScopesListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $this->registerAllowedScopes();
    }

    private function registerAllowedScopes(): void
    {
        $collectedAnnotations = AnnotationCollector::getMethodsByAnnotation(Scope::class);
        foreach ($collectedAnnotations as $annotationData) {
            $this->registerScope(
                $annotationData['class'],
                $annotationData['method'],
                $annotationData['annotation']
            );
        }
    }

    private function registerScope(string $target, string $method, ?object $scope): void
    {
        AllowedScopes::addTarget(
            target: $target,
            method: $method,
            scope: $scope
        );
    }
}
