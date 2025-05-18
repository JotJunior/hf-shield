<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Stringable\Str;
use Jot\HfRepository\Command\HfFriendlyLinesTrait;
use Jot\HfShield\Entity\Scope\Scope;
use Jot\HfShield\Repository\ScopeRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;

use function Hyperf\Support\make;
use function Hyperf\Translation\__;

#[Command]
class OAuthScopeCommand extends HyperfCommand
{
    use HfFriendlyLinesTrait;

    #[Inject]
    protected ScopeRepository $repository;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('oauth:scope');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription(__('hf-shield.oauth_scope_description'));
        $this->addArgument('action', InputArgument::REQUIRED, __('hf-shield.action_description'));
        $this->addUsage('oauth:scope list');
        $this->addUsage('oauth:scope sync');
        $this->addUsage('oauth:scope create scope.fqdn.name "Scope description"');
        $this->addUsage('oauth:scope delete scope.fqdn.name');
    }

    public function handle()
    {
        $sub = $this->input->getArgument('action');

        if (method_exists($this, $sub)) {
            $this->{$sub}();
        }
    }

    protected function list(): void
    {
        $list = $this->repository->paginate(['_sort' => 'id:asc'], 1, 1000);
        $maxLength = 0;

        foreach ($list['data'] as $item) {
            $maxLength = max($maxLength, strlen($item['id']));
        }

        foreach ($list['data'] as $item) {
            $this->success(str_pad($item['id'], $maxLength, ' ', STR_PAD_RIGHT) . '   ' . $item['name']);
        }
    }

    protected function sync(): void
    {
        $collectedAnnotations = AnnotationCollector::getMethodsByAnnotation(\Jot\HfShield\Annotation\Scope::class);

        foreach ($collectedAnnotations as $annotationData) {
            $scopes = (array) $annotationData['annotation']->allow;
            foreach ($scopes as $scope) {
                try {
                    $this->registerScope($scope);
                } catch (Throwable $th) {
                    $this->failed($th->getMessage());
                }
            }
        }
    }

    protected function registerScope(string $scope): void
    {
        $parts = explode(':', $scope);
        $baseScope = [];
        foreach ($parts as $part) {
            $baseScope[] = $part;
            $finalScope = implode(':', $baseScope);
            if (count($baseScope) < 3) {
                $finalScope .= ':';
            }

            if ($this->repository->exists($finalScope)) {
                $this->warning(__('hf-shield.scope_already_registered', ['scope' => $scope]));
            } else {
                $description = match ($baseScope[2] ?? null) {
                    'list' => sprintf('List all %s', Str::plural($parts[1])),
                    'view' => sprintf('View a single %s', Str::singular($parts[1])),
                    'create' => sprintf('Create a new %s', Str::singular($parts[1])),
                    'update' => sprintf('Update an existing %s', Str::singular($parts[1])),
                    'delete' => sprintf('Delete a %s', Str::singular($parts[1])),
                    default => sprintf('Scope %s', $finalScope),
                };
                $this->success($scope . ' - ' . $description);

                $this->repository->create(make(Scope::class, [
                    'data' => [
                        'id' => $finalScope,
                        'name' => $description,
                        'domain' => __(sprintf('hf-shield.scopes.%s', $parts[0])),
                        'resource' => empty($parts[1]) ? null : __(sprintf('hf-shield.scopes.%s', $parts[1])),
                        'action' => empty($parts[2]) ? null : $parts[2],
                    ],
                ]));
            }
        }
    }

    protected function create(): void
    {
        $name = $this->ask(__('hf-shield.name'));
        $description = $this->ask(__('hf-shield.description'));

        $scope = make(Scope::class, [
            'data' => [
                'id' => Str::snake($name),
                'name' => $description,
            ],
        ]);

        try {
            $this->repository->create($scope);
            $this->success(__('hf-shield.scope_created_successfully'));
        } catch (Throwable $th) {
            $this->failed($th->getMessage());
            return;
        }
    }
}
