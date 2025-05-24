<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Entity\User;

use DateTimeInterface;
use Hyperf\Stringable\Str;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfRepository\Entity\Traits\HasLogicRemovalTrait as HasLogicRemoval;
use Jot\HfRepository\Entity\Traits\HasTimestampsTrait as HasTimestamps;

#[SA\Schema(schema: 'jot.shield.entity.user.user')]
class User extends Entity
{
    use HasLogicRemoval;
    use HasTimestamps;

    public const SEARCHABLE = ['name', 'email', 'phone', 'federal_document'];

    #[SA\Property(
        property: 'id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: 'legacy_id',
        description: 'An alias of legacy id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $legacyId = null;

    #[SA\Property(
        property: 'name',
        type: 'string',
        example: ''
    )]
    protected ?string $name = null;

    #[SA\Property(
        property: 'language',
        type: 'string',
        example: ''
    )]
    protected ?string $language = null;

    #[SA\Property(
        property: 'email',
        type: 'string',
        example: ''
    )]
    protected ?string $email = null;

    #[SA\Property(
        property: 'phone',
        type: 'string',
        example: ''
    )]
    protected ?string $phone = null;

    #[SA\Property(
        property: 'federal_document',
        type: 'string',
        example: ''
    )]
    protected ?string $federalDocument = null;

    #[SA\Property(
        property: 'document_type',
        type: 'string',
        example: ''
    )]
    protected ?string $documentType = null;

    #[SA\Property(
        property: 'picture',
        type: 'string',
        example: ''
    )]
    protected ?string $picture = null;

    #[SA\Property(
        property: 'password_salt',
        type: 'string',
        example: ''
    )]
    protected ?string $passwordSalt = null;

    #[SA\Property(
        property: 'password',
        type: 'string',
        example: ''
    )]
    protected ?string $password = null;

    #[SA\Property(
        property: 'status',
        type: 'string',
        example: ''
    )]
    protected ?string $status = null;

    #[SA\Property(
        property: 'tag',
        type: 'string',
        example: ''
    )]
    protected null|array|string $tags = null;

    #[SA\Property(
        property: 'custom_setting',
        ref: '#/components/schemas/app.entity.user.custom_setting',
        x: ['php_type' => '\Jot\HfShield\Entity\User\CustomSetting']
    )]
    protected mixed $customSettings = null;

    #[SA\Property(
        property: 'tenant',
        ref: '#/components/schemas/app.entity.user.tenant',
        x: ['php_type' => '\Jot\HfShield\Entity\User\Tenant']
    )]
    protected ?Tenant $tenant = null;

    #[SA\Property(
        property: 'tenants',
        type: 'array',
        items: new SA\Items(ref: '#/components/schemas/app.entity.user.tenant'),
        x: ['php_type' => '\Jot\HfShield\Entity\User\Tenant[]']
    )]
    protected ?array $tenants = null;

    #[SA\Property(
        property: 'user_id',
        description: 'An alias of user id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $userId = null;

    #[SA\Property(
        property: 'created_at',
        type: 'string',
        format: 'date-time',
        x: ['php_type' => '\DateTime']
    )]
    protected ?DateTimeInterface $createdAt = null;

    #[SA\Property(
        property: 'updated_at',
        type: 'string',
        format: 'date-time',
        x: ['php_type' => '\DateTime']
    )]
    protected ?DateTimeInterface $updatedAt = null;

    #[SA\Property(
        property: 'deleted',
        type: 'boolean',
        readOnly: true,
        example: true
    )]
    protected null|bool|int $deleted = null;


    public function addSalt(): User
    {
        $this->passwordSalt = Str::uuid()->toString();
        return $this;
    }

    public function getPasswordSalt(): ?string
    {
        return $this->passwordSalt;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function hasCompany(string $tenantId, string $companyId): bool
    {
        if (empty($this->tenants)) {
            return false;
        }

        $tenant = current(
            array_filter($this->tenants, function ($tenant) use ($tenantId) {
                return $tenant->id === $tenantId;
            })
        );

        if (empty($tenant)) {
            return false;
        }

        return boolval(
            array_filter(
                $tenant->customers,
                function ($company) use ($companyId) {
                    return $company->id === $companyId;
                }
            )
        );
    }

    public function getCustomers(string $tenantId): ?array
    {
        if (empty($this->tenants)) {
            return null;
        }

        $tenant = current(
            array_filter($this->tenants, function ($tenant) use ($tenantId) {
                return $tenant->id === $tenantId;
            })
        );

        if (empty($tenant)) {
            return null;
        }

        return $tenant->customers;
    }
}
