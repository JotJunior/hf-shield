<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Service;

use DateTime;
use Hyperf\Di\Annotation\Inject;
use Jot\HfRepository\Entity\EntityInterface;
use Jot\HfShield\Entity\UserCode\UserCode;
use Jot\HfShield\Event\OtpEvent;
use Jot\HfShield\Exception\UnauthorizedUserException;
use Jot\HfShield\Repository\UserCodeRepository;
use Jot\HfShield\Repository\UserRepository;
use League\OAuth2\Server\CryptTrait;
use Psr\EventDispatcher\EventDispatcherInterface;

use function Hyperf\Support\make;

class OtpService
{
    use CryptTrait;

    #[Inject]
    protected UserRepository $userRepository;

    #[Inject]
    protected UserCodeRepository $userCodeRepository;

    #[Inject]
    protected EventDispatcherInterface $dispatcher;

    public function create(array $data): array
    {
        $user = $this->getUserFromFederalDocument($data['federal_document'], $data['_tenant_id']);
        if (empty($user)) {
            throw new UnauthorizedUserException();
        }

        return [
            'data' => $this->generateCode($user),
            'result' => 'success',
            'message' => null,
        ];
    }

    private function getUserFromFederalDocument(string $federalDocument, string $tenantId): EntityInterface
    {
        return $this->userRepository->first([
            // 'tenant_id' => $tenantId,
            'federal_document' => $federalDocument,
            'deleted' => false,
        ]);
    }

    private function generateCode(EntityInterface $user): string
    {
        $randomNumber = (string) rand(1, 999999);
        $newCode = str_pad($randomNumber, 6, '0', STR_PAD_LEFT);

        $userCode = make(name: UserCode::class, parameters: [
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'tenant' => $user->tenant->toArray(),
                'status' => 'active',
                'code' => $this->encrypt(
                    unencryptedData: sprintf(
                        '%s|%s',
                        (new DateTime('+5 min'))->format(DATE_ATOM),
                        $newCode
                    )
                ),
            ],
        ]);

        $code = $this->userCodeRepository->create($userCode);

        $this->dispatcher->dispatch(new OtpEvent(code: $newCode, recipient: $user->phone));

        return $code->getId();
    }
}
