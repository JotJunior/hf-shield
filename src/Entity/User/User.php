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
use Jot\HfValidator\Annotation as VA;

#[SA\Schema(schema: 'jot.shield.entity.user.user')]
class User extends Entity
{
    use HasLogicRemoval;
    use HasTimestamps;

    #[SA\Property(
        property: 'created_at',
        type: 'string',
        format: 'date-time',
        x: ['php_type' => '\DateTime']
    )]
    protected ?DateTimeInterface $createdAt = null;

    #[SA\Property(
        property: 'custom_setting',
        type: 'string',
        example: ''
    )]
    protected ?string $customSettings = null;

    #[SA\Property(
        property: 'deleted',
        type: 'boolean',
        readOnly: true,
        example: true
    )]
    protected null|bool|int $deleted = null;

    #[SA\Property(
        property: 'email',
        type: 'string',
        example: ''
    )]
    #[VA\Email]
    #[VA\Required(onUpdate: false)]
    #[VA\Unique(index: 'users', field: 'email')]
    protected ?string $email = null;

    #[SA\Property(
        property: 'federal_document',
        type: 'string',
        example: ''
    )]
    #[VA\Required(onUpdate: false)]
    #[VA\Unique(index: 'users', field: 'federal_document')]
    #[VA\CPF]
    protected ?string $federalDocument = null;

    #[SA\Property(
        property: 'document_type',
        type: 'string',
        enum: ['CPF', 'RG', 'Passport', 'IE', 'Other'],
        example: 'CPF'
    )]
    #[VA\Required(onUpdate: false)]
    protected ?string $documentType = null;

    #[SA\Property(
        property: 'id',
        type: 'string',
        readOnly: true,
        example: ''
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: 'name',
        type: 'string',
        example: ''
    )]
    #[VA\Required(onUpdate: false)]
    #[VA\StringLength(min: 3, max: 100)]
    protected ?string $name = null;

    #[SA\Property(
        property: 'password',
        type: 'string',
        example: ''
    )]
    #[VA\Required(onUpdate: false)]
    #[VA\Password]
    protected ?string $password = null;

    #[SA\Property(
        property: 'password_salt',
        type: 'string',
        example: ''
    )]
    protected ?string $passwordSalt = null;

    #[SA\Property(
        property: 'phone',
        type: 'string',
        example: ''
    )]
    #[VA\Unique(index: 'users', field: 'phone')]
    protected ?string $phone = null;

    #[SA\Property(
        property: 'picture',
        type: 'string',
        example: ''
    )]
    #[VA\Url]
    protected ?string $picture = null;

    #[SA\Property(
        property: 'status',
        type: 'string',
        example: ''
    )]
    #[VA\Enum(values: ['active', 'inactive', 'pending'])]
    protected ?string $status = null;

    #[SA\Property(
        property: 'tenants',
        type: 'array',
        items: new SA\Items(ref: '#/components/schemas/jot.shield.entity.user.tenant'),
        x: ['php_type' => '\Jot\HfShield\Entity\User\Tenant[]']
    )]
    protected ?array $tenants = null;

    #[SA\Property(
        property: 'updated_at',
        type: 'string',
        format: 'date-time',
        x: ['php_type' => '\DateTime']
    )]
    protected ?DateTimeInterface $updatedAt = null;

    #[SA\Property(
        property: 'user_id',
        description: 'An alias of user id',
        type: 'string',
        readOnly: true,
        example: 'user_9876'
    )]
    protected ?string $userIdentifier = null;

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
}
