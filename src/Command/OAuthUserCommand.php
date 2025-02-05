<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Di\Annotation\Inject;
use Jot\HfOAuth2\Entity\User\User;
use Jot\HfOAuth2\Repository\UserRepository;
use Jot\HfRepository\Command\HfFriendlyLinesTrait;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use function Hyperf\Support\make;

#[Command]
class OAuthUserCommand extends HyperfCommand
{

    use HfFriendlyLinesTrait;

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
            $this->$sub();
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
        $username = $this->ask('Username:');
        $user = $this->repository->first(['email' => $username]);

        if (empty($user)) {
            $this->failed('User not found.');
            return;
        }

        $scopes = [];
        foreach ($this->repository->getScopeList()['data'] as $scope) {
            $selected = $this->ask(sprintf('Add scope %s? (y/n):', $scope['name']), 'n');
            if ($selected === 'y') {
                $scopes[] = [
                    'id' => $scope['id'],
                    'name' => $scope['name']
                ];
            }
        }

        if (empty($scopes)) {
            $this->warning('No scopes selected.');
            return;
        }

        try {
            $result = $this->repository->updateScopes($user, $scopes)->toArray();
        } catch (EntityValidationWithErrorsException $th) {
            $this->failed($th->getMessage());
            foreach ($th->getErrors() as $field => $message) {
                $this->failed('<fg=#FFCC00;options=bold>%s:</> %s', [$field, $message[0]]);
            }
            return;
        } catch (\Throwable $th) {
            $this->failed('Error updating user: %s', [$th->getMessage()]);
            return;
        }
        $this->success('All scopes updated successfully.');
        foreach ($result['scopes'] as $scope) {
            $this->success('  <fg=#FFCC00>%s</> [%s]', [$scope['name'], $scope['id']]);
        }
    }

    protected function create(): void
    {
        $tenant = $this->ask('Tenant: <fg=yellow>(*)</> <fg=white>[Type "-" for get the tenant list]</>');
        if ($tenant === '-') {
            $tenants = [];
            foreach ($this->repository->getTenantList()['data'] as $idx => $item) {
                $tenants[] = $item;
                $this->success('<fg=yellow>%d</> -  %s : %s', [$idx + 1, $item['id'], $item['name']]);
            }
            $pickedNumber = $this->ask('Pick a number: ');
            $tenant = $tenants[(int)$pickedNumber - 1]['id'] ?? null;
            if (!$tenant) {
                $this->failed('Wrong tenant number.');
                return;
            }
            $this->success('Tenant selected: %s', [$tenant]);
        }

        $name = $this->ask('Name: <fg=yellow>(*)</>');
        $email = $this->retryIf('exists', 'E-mail', 'email', ['tenant.id' => $tenant]);
        $phone = $this->retryIf('exists', 'Phone', 'phone', ['tenant.id' => $tenant]);
        $federalDocument = $this->retryIf('exists', 'Federal Document', 'federal_document', ['tenant.id' => $tenant]);
        $client = $this->ask('Client:');

        do {
            $password = $this->secret('Password: <fg=yellow>(*)</>');
            $repeatPassword = $this->secret('Repeat Password: <fg=yellow>(*)</>');
            if ($repeatPassword !== $password) {
                $this->warning('Passwords must match.');
            }
        } while ($password !== $repeatPassword);

        $payload = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'tenant' => ['id' => $tenant],
            'federal_document' => $federalDocument,
            'password' => $password,
        ];
        if ($client) {
            $payload['client'] = $client;
        }
        $data = make(User::class, [
            'data' => $payload
        ]);

        try {
            $this->repository->create($data);
            $this->success('User created successfully.');
        } catch (EntityValidationWithErrorsException $th) {
            foreach ($th->getErrors() as $field => $message) {
                $this->failed('<fg=#FFCC00;options=bold>%s:</> %s', [$field, $message[0]]);
            }
            return;
        }

    }

    protected function retryIf(string $condition, string $label, string $field, array $conditions = [], bool $allowEmpty = false)
    {
        do {
            $value = $this->ask(sprintf('%s:%s</>', $label, $allowEmpty ? '' : ' <fg=yellow>(*)'));
            if (!$value && $allowEmpty) {
                return $value;
            }

            $fieldExists = !empty($this->repository->first([$field => $value, ...$conditions]));

            if ($this->validateCondition($fieldExists, $condition, $label, $value)) {
                return $value;
            }
        } while (true);
    }

    private function validateCondition(bool $fieldExists, string $condition, string $label, string $value): bool
    {
        if ($condition === 'exists' && !$fieldExists) {
            return true;
        }

        if ($condition === 'exists' && $fieldExists) {
            $this->warning('%s %s is already used.', [$label, $value]);
        }

        if ($condition === 'missing' && $fieldExists) {
            return true;
        }

        if ($condition === 'missing' && !$fieldExists) {
            $this->warning('%s %s not found.', [$label, $value]);
        }

        return false;
    }

}
