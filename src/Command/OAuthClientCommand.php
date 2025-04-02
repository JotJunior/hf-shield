<?php

declare(strict_types=1);

namespace Jot\HfShield\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Di\Annotation\Inject;
use Jot\HfRepository\Command\HfFriendlyLinesTrait;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Jot\HfShield\Entity\Client\Client;
use Jot\HfShield\Repository\ClientRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use function Hyperf\Support\make;

#[Command]
class OAuthClientCommand extends AbstractCommand
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

        foreach ($list['data'] as $item) {
            $this->success('%s : %s', [$item['id'], $item['name']]);
        }
    }

    protected function create(): void
    {
        $tenant = $this->selectTenant();
        $name = $this->ask(__('hf-shield.name') . ': <fg=yellow>(*)</>');
        $email = $this->ask(__('hf-shield.redirect_uri') . ': <fg=yellow>(*)</>');

        $client = make(Client::class, [
            'data' => [
                'name' => $name,
                'redirect_uri' => $email,
                'tenant' => ['id' => $tenant],
                'status' => 'active',
            ]
        ]);

        try {
            list($plainSecret, $result) = $this->repository->createNewClient($client);
            $clientId = $result->toArray()['id'];
            $this->success(__('hf-shield.client_id') . ':     <fg=#FFCC00>%s</>', [$clientId]);
            $this->success('Client Secret: <fg=#FFCC00>%s</>', [$plainSecret]);
            $this->success(__('hf-shield.save_secret_warning'));
        } catch (EntityValidationWithErrorsException $th) {
            foreach ($th->getErrors() as $field => $message) {
                $this->failed('%s: %s', [$field, $message[0]]);
            }
            return;
        }

    }

}
