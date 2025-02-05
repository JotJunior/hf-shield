<?php

declare(strict_types=1);

namespace Jot\HfShield\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Stringable\Str;
use Jot\HfShield\Entity\Scope\Scope;
use Jot\HfRepository\Command\HfFriendlyLinesTrait;
use Jot\HfShield\Repository\ScopeRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use function Hyperf\Support\make;

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
        $this->setDescription('Create an OAuth scope');
        $this->addArgument('sub', InputArgument::REQUIRED, 'Sub command');
        $this->addUsage('oauth:scope list');
        $this->addUsage('oauth:scope sync');
        $this->addUsage('oauth:scope create scope.fqdn.name "Scope description"');
        $this->addUsage('oauth:scope delete scope.fqdn.name');
    }

    public function handle()
    {

        $sub = $this->input->getArgument('sub');

        if (method_exists($this, $sub)) {
            $this->$sub();
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

    protected function create(): void
    {
        $name = $this->ask('Name');
        $description = $this->ask('Description');

        $scope = make(Scope::class, [
            'data' => [
                'id' => Str::snake($name),
                'name' => $description,
            ]
        ]);

        try {
            $this->repository->create($scope);
            $this->success('Scope created successfully.');
        } catch (\Throwable $th) {
            $this->failed($th->getMessage());
            return;
        }

    }

    protected function sync(): void
    {
        $collectedAnnotations = AnnotationCollector::getMethodsByAnnotation(\Jot\HfShield\Annotation\Scope::class);

        foreach ($collectedAnnotations as $annotationData) {
            $scopes = (array)$annotationData['annotation']->allow;
            foreach ($scopes as $scope) {
                $this->registerScope($scope);
            }
        }
    }

    protected function registerScope(string $scope): void
    {
        if ($this->repository->exists($scope)) {
            $this->warning('Scope %s is already registered.', [$scope]);
            return;
        }

        $description = $this->ask(sprintf('%s description: ', $scope));
        $this->repository->create(make(Scope::class, [
            'data' => [
                'id' => $scope,
                'name' => $description,
            ]
        ]));
    }
}
