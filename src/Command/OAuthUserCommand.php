<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
 * @license  MIT
 */

namespace Jot\HfShield\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Di\Annotation\Inject;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Jot\HfRepository\Exception\RepositoryUpdateException;
use Jot\HfShield\Entity\User\User;
use Jot\HfShield\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;

use function Hyperf\Support\make;
use function Hyperf\Translation\__;

#[Command]
class OAuthUserCommand extends AbstractCommand
{
    #[Inject]
    protected UserRepository $repository;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('oauth:user');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Create an OAuth user');
        $this->addArgument('sub', InputArgument::REQUIRED, 'Sub command');
        $this->addUsage('oauth:user list');
        $this->addUsage('oauth:user create');
        $this->addUsage('oauth:user scopes');
        $this->addUsage('oauth:user change-password');
        $this->addUsage('oauth:user delete user-id');
    }

    public function handle()
    {
        $sub = $this->input->getArgument('sub');

        if (method_exists($this, $sub)) {
            $this->{$sub}();
        }
    }

    protected function list(): void
    {
        $list = $this->repository->paginate([], 1, 1000);

        foreach ($list['data'] as $item) {
            $this->success('%s : %s', [$item['id'], $item['name']]);
        }
    }

    protected function scopes()
    {
        $username = $this->ask(__('hf-shield.username') . ':');
        $user = $this->repository->first(['email' => $username]);

        if (empty($user)) {
            $this->failed(__('hf-shield.user_not_found'));
            return;
        }

        $scopes = [];
        foreach ($this->repository->retrieveScopeList()['data'] as $scope) {
            $selected = $this->ask(sprintf(__('hf-shield.add_scope_prompt'), $scope['name']), 'n');
            if ($selected === 'y') {
                $scopes[] = [
                    'id' => $scope['id'],
                    'name' => $scope['name'],
                ];
            }
        }

        if (empty($scopes)) {
            $this->warning(__('hf-shield.no_scopes_selected'));
            return;
        }

        try {
            $result = $this->repository->updateScopes($user, $scopes)->toArray();
        } catch (RepositoryUpdateException $th) {
            $this->failed($th->getMessage());
            return;
        } catch (Throwable $th) {
            $this->failed('Error updating user: %s', [$th->getMessage()]);
            return;
        }
        $this->success(__('hf-shield.all_scopes_updated_successfully'));
        foreach ($result['scopes'] as $scope) {
            $this->success('  <fg=#FFCC00>%s</> [%s]', [$scope['name'], $scope['id']]);
        }
    }

    protected function create(): void
    {
        $tenant = $this->selectTenant();
        $client = $this->selectClient($tenant);
        $name = $this->ask(__('hf-shield.name') . ': <fg=yellow>(*)</>');
        $email = $this->retryIf('exists', __('hf-shield.email'), 'email', ['tenant.id' => $tenant]);
        $phone = $this->retryIf('exists', __('hf-shield.phone'), 'phone', ['tenant.id' => $tenant]);
        $federalDocument = $this->retryIf('exists', __('hf-shield.federal_document'), 'federal_document', ['tenant.id' => $tenant]);

        do {
            $password = $this->secret(__('hf-shield.password') . ': <fg=yellow>(*)</>');
            $repeatPassword = $this->secret(__('hf-shield.repeat_password') . ': <fg=yellow>(*)</>');
            if ($repeatPassword !== $password) {
                $this->warning(__('hf-shield.passwords_must_match'));
            }
        } while ($password !== $repeatPassword);

        $payload = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'tenant' => ['id' => $tenant],
            'federal_document' => $federalDocument,
            'password' => $password,
            'status' => 'active',
        ];
        if ($client) {
            $payload['client'] = ['id' => $client];
        }
        $data = make(User::class, [
            'data' => $payload,
        ]);

        try {
            $this->repository->create($data);
            $this->success(__('hf-shield.user_created_successfully'));
        } catch (EntityValidationWithErrorsException $th) {
            foreach ($th->getErrors() as $field => $message) {
                $this->failed('<fg=#FFCC00;options=bold>%s:</> %s', [$field, $message[0]]);
            }
            return;
        }
    }
}
