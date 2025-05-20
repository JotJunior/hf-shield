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

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Hyperf\Di\Annotation\Inject;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Jot\HfRepository\Service\AbstractService;
use Jot\HfShield\Dto\Profile\User\UserPasswordDto;
use Jot\HfShield\Dto\Profile\User\UserSessionDto;
use Jot\HfShield\Entity\User\User as Entity;
use Jot\HfShield\Helper\Base64ImageHandler;
use Jot\HfShield\Repository\UserRepository;
use function Hyperf\Support\make;
use function Hyperf\Translation\__;

class ProfileService extends AbstractService
{
    public const CACHE_PREFIX_PROFILE = 'profile:entity';

    public const CACHE_PREFIX_USER = 'user:entity';

    protected string $repositoryClass = UserRepository::class;

    protected string $entityClass = Entity::class;

    #[Inject]
    protected Base64ImageHandler $imageHandler;

    #[Cacheable(prefix: self::CACHE_PREFIX_PROFILE, ttl: 600, listener: self::CACHE_PREFIX_PROFILE)]
    public function getProfileData(string $id): array
    {
        $entity = $this->repository->find($id);

        return [
            'data' => $entity?->hide(['password', 'password_salt', '@version', '@timestamp', 'tenants'])?->toArray(),
            'result' => 'success',
            'message' => null,
        ];
    }

    public function getSessionData(string $id): array
    {
        $userData = $this->repository->find($id)->toArray();
        $userData['display_name'] = $userData['name'];
        $userData['photo_url'] = $userData['picture'] ?? null;
        $user = make(UserSessionDto::class, ['data' => $userData])->asCamel()->toArray();
        $user['photoUrl'] = $user['photoUrl'] ?? null;
        $user['shortcuts'] = [];
        $user['settings'] = [...$userData['custom_settings'] ?? [], 'language' => $userData['language'] ?? 'pt'];
        return $user;
    }

    public function updateSettings(string $id, array $settings): array
    {
        $userData = $this->repository->find($id)
            ->hide(['password', 'password_salt'])
            ->toArray();
        $userData['custom_settings'] = $settings;
        $entity = make($this->entityClass, ['data' => $userData]);

        $result = $this->repository->update($entity)->toArray();

        $this->dispatcher->dispatch(new DeleteListenerEvent(self::CACHE_PREFIX_USER, [$id]));
        $this->dispatcher->dispatch(new DeleteListenerEvent(self::CACHE_PREFIX_PROFILE, [$id]));

        return $result;
    }

    public function updatePassword(string $id, array $data): array
    {
        $errors = [];
        if (empty($data['password'])) {
            $errors['password'] = [__('hf-shield.empty_password')];
        }
        if (empty($data['current_password'])) {
            $errors['current_password'] = [__('hf-shield.empty_current_password')];
        }

        $user = $this->repository->find($id)->toArray();
        if (! $this->repository->isPasswordValid($user['password'], $data['current_password'], $user['password_salt'])) {
            $errors['current_password'] = [__('hf-shield.invalid_password')];
        }

        if ($errors) {
            throw new EntityValidationWithErrorsException($errors);
        }

        unset($data['current_password']);
        $userData = array_merge($this->repository->find($id)->toArray(), $data);
        if (! empty($userData['tags'])) {
            $userData['tags'] = array_values(array_filter($userData['tags'], function ($tag) {
                return $tag !== 'require_password_change';
            }));
        }

        $entity = make(UserPasswordDto::class, ['data' => $userData]);

        $result = $this->repository->updatePassword($entity)->toArray();

        $this->dispatcher->dispatch(new DeleteListenerEvent(self::CACHE_PREFIX_USER, [$id]));
        $this->dispatcher->dispatch(new DeleteListenerEvent(self::CACHE_PREFIX_PROFILE, [$id]));

        return $result;
    }

    public function updateProfile(string $id, array $data): array
    {
        if (! empty($data['picture']) && str_starts_with($data['picture'], 'data:image')) {
            $data['picture'] = $this->imageHandler->uploadToS3($data['picture'])['url'];
        }

        unset(
            $data['tenants'],
            $data['password'],
            $data['password_salt'],
            $data['tags'],
            $data['status'],
            $data['email'],
            $data['phone'],
            $data['federal_document'],
        );

        $entity = make($this->entityClass, ['data' => ['id' => $id, ...$data]]);
        $result = $this->repository->updateProfile($entity);

        $this->dispatcher->dispatch(new DeleteListenerEvent(self::CACHE_PREFIX_USER, [$id]));
        $this->dispatcher->dispatch(new DeleteListenerEvent(self::CACHE_PREFIX_PROFILE, [$id]));

        return [
            'data' => $result->toArray(),
            'result' => 'success',
            'message' => null,
        ];
    }

}
