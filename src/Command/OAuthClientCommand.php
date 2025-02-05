<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Di\Annotation\Inject;
use Jot\HfOAuth2\Entity\User\User;
use Jot\HfOAuth2\Repository\ClientRepository;
use Jot\HfRepository\Command\HfFriendlyLinesTrait;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use function Hyperf\Support\make;

#[Command]
class OAuthClientCommand extends HyperfCommand
{

    use HfFriendlyLinesTrait;

    #[Inject]
    protected ClientRepository $repository;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('oauth:client');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Create an OAuth Client');
        $this->addArgument('sub', InputArgument::REQUIRED, 'Sub command');
        $this->addUsage('oauth:user list');
        $this->addUsage('oauth:user create');
        $this->addUsage('oauth:user delete client-id');
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
        $email = $this->ask('Redirect URI: <fg=yellow>(*)</>');
        $tenant = $this->ask('Tenant ID: <fg=yellow>(*)</>');

        $scope = make(User::class, [
            'data' => [
                'name' => $name,
                'redirect_uri' => $email,
                'tenant' => ['id' => $tenant],
            ]
        ]);

        try {
            $this->repository->create($scope);
            $this->success('User created successfully.');
        } catch (EntityValidationWithErrorsException $th) {
            foreach ($th->getErrors() as $field => $message) {
                $this->failed('%s: %s', [$field, $message[0]]);
            }
            return;
        }

    }

    public function tabResult(array $items)
    {
        $lengths = [];
        foreach ($items as $item) {
            $lengths[] = max(strlen($item), 50);
        }
        return $lengths;
    }

}
