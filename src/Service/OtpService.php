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
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Jot\HfRepository\Entity\EntityInterface;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Jot\HfRepository\Exception\RepositoryUpdateException;
use Jot\HfShield\Entity\UserCode\UserCode;
use Jot\HfShield\Event\OtpEvent;
use Jot\HfShield\Exception\InvalidOtpCodeException;
use Jot\HfShield\Exception\UnauthorizedUserException;
use Jot\HfShield\Repository\UserCodeRepository;
use Jot\HfShield\Repository\UserRepository;
use League\OAuth2\Server\CryptTrait;
use Psr\EventDispatcher\EventDispatcherInterface;

use function Hyperf\Support\make;
use function Hyperf\Translation\__;

class OtpService
{
    use CryptTrait;

    public const OTP_EXPIRATION_TIME = 300;

    #[Inject]
    protected UserRepository $userRepository;

    #[Inject]
    protected UserCodeRepository $userCodeRepository;

    #[Inject]
    protected EventDispatcherInterface $dispatcher;

    public function __construct(private readonly ConfigInterface $config)
    {
        $this->setEncryptionKey($this->config->get('hf_shield.encryption_key', ''));
    }

    #[Cacheable(prefix: 'otp:create', ttl: self::OTP_EXPIRATION_TIME)]
    public function create(array $data): array
    {
        $user = $this->getUserFromFederalDocument($data['federal_document'], $data['_tenant_id'] ?? null);
        if (empty($user)) {
            throw new UnauthorizedUserException();
        }

        return [
            'data' => $this->generateCode($user),
            'result' => 'success',
            'message' => null,
        ];
    }

    public function validateCode(array $data): array
    {
        $otp = $this->userCodeRepository->find($data['otp_id']);

        if (empty($otp) || ! $this->isValidCode($data['code'], $otp)) {
            throw new InvalidOtpCodeException(__('hf-shield.invalid_otp_code'));
        }

        $this->userCodeRepository->update(
            make(UserCode::class, ['data' => ['id' => $otp->id, 'status' => 'validated']])
        );

        return [
            'data' => $data['otp_id'],
            'result' => 'success',
            'message' => __('hf-shield.otp_code_validated'),
        ];
    }

    /**
     * @throws EntityValidationWithErrorsException
     * @throws RepositoryUpdateException
     */
    public function changePassword(array $data): array
    {
        $otp = $this->userCodeRepository->find($data['otp_id']);

        if (empty($otp) || ! $otp->status !== 'validated') {
            throw new InvalidOtpCodeException(__('hf-shield.invalid_otp_code'));
        }

        $user = $this->userRepository->find($otp->user->getId());

        if (empty($user)) {
            throw new InvalidOtpCodeException(__('hf-shield.invalid_otp_code'));
        }

        $user->hydrate([
            'password' => $data['password'],
        ]);

        $this->userRepository->updatePassword($user);

        return [
            'data' => $data['otp_id'],
            'result' => 'success',
            'message' => __('hf-shield.password_changed_successfully'),
        ];
    }

    private function getUserFromFederalDocument(string $federalDocument, ?string $tenantId): ?EntityInterface
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

    private function isValidCode(string $code, EntityInterface $otp, string $requiredStatus = 'active'): bool
    {
        $decrypted = explode('|', $this->decrypt($otp->code));

        $now = new DateTime('now');
        $exp = new DateTime($decrypted[0]);
        if ($now > $exp) {
            throw new InvalidOtpCodeException(__('hf-shield.expired_otp_code'));
        }

        return $otp->status === $requiredStatus && $decrypted[1] === $code;
    }
}
