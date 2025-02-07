<?php

declare(strict_types=1);

namespace Jot\HfShield\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Di\Annotation\Inject;
use Jot\HfRepository\Command\HfFriendlyLinesTrait;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Jot\HfShield\Entity\Tenant\Tenant;
use Jot\HfShield\Repository\TenantRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use function Hyperf\Support\make;

#[Command]
class OAuthTenantCommand extends HyperfCommand
{

    use HfFriendlyLinesTrait;

    #[Inject]
    protected TenantRepository $repository;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('oauth:tenant');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Create an OAuth Tenant');
        $this->addArgument('sub', InputArgument::REQUIRED, 'Sub command');
        $this->addUsage('oauth:tenant list');
        $this->addUsage('oauth:tenant create');
        $this->addUsage('oauth:tenant change-password');
        $this->addUsage('oauth:tenant delete tenant-id');
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
        $list = $this->repository->paginate([], 1, 1000);
        $maxLength = 0;

        foreach ($list['data'] as $item) {
            $this->success('%s : %s', [$item['id'], $item['name']]);
        }
    }

    protected function create(): void
    {
        $name = $this->ask('Name: <fg=yellow>(*)</>');
        $ips = $this->ask('IPs: <fg=yellow>(*)</> <fg=white>[separate by comma]</>');
        $domains = $this->ask('Domains: <fg=yellow>(*)</> <fg=white>[separate by comma]</>');

        $tenant = make(Tenant::class, [
            'data' => [
                'name' => $name,
                'ips' => explode(',', str_replace(' ', '', $ips)),
                'domains' => explode(',', str_replace(' ', '', $domains)),
            ]
        ]);

        try {
            $result = $this->repository->create($tenant)->toArray();
            $this->success('Tenant ID: %s', [$result['id']]);
        } catch (EntityValidationWithErrorsException $th) {
            foreach ($th->getErrors() as $field => $message) {
                $this->failed('%s: %s', [$field, $message[0]]);
            }
            return;
        }

    }


}
