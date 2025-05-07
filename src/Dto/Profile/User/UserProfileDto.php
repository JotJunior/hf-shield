<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Dto\Profile\User;

use DateTimeInterface;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfRepository\Entity\Traits\HasLogicRemovalTrait as HasLogicRemoval;
use Jot\HfRepository\Entity\Traits\HasTimestampsTrait as HasTimestamps;
use Jot\HfShield\Entity\User\Tenant;
use Jot\HfValidator\Annotation as VA;

class UserProfileDto extends Entity
{
    use HasLogicRemoval;
    use HasTimestamps;

    public const SEARCHABLE = ['name.search', 'email.search', 'federal_document.search'];

    protected ?DateTimeInterface $createdAt = null;

    protected mixed $customSettings = null;

    protected null|bool|int $deleted = null;

    #[VA\Email]
    #[VA\Required]
    #[VA\Unique(index: 'users', field: 'email', level: 'tenant')]
    protected ?string $email = null;

    #[SA\Property(
        property: 'tags',
        type: 'array',
        example: ''
    )]
    protected null|array|string $tags = null;

    #[VA\Required(onUpdate: false)]
    #[VA\Unique(index: 'users', field: 'federal_document', level: 'tenant')]
    #[VA\CPF]
    protected ?string $federalDocument = null;

    #[VA\Required(onUpdate: false)]
    protected ?string $documentType = null;

    protected ?string $id = null;

    #[VA\Required(onUpdate: false)]
    #[VA\StringLength(min: 3, max: 100)]
    protected ?string $name = null;

    #[VA\Required(onUpdate: false)]
    #[VA\Password]
    protected ?string $password = null;

    protected ?string $passwordSalt = null;

    #[VA\Unique(index: 'users', field: 'phone')]
    protected ?string $phone = null;

    #[VA\Url]
    protected ?string $picture = null;

    #[VA\Enum(values: ['active', 'inactive', 'pending'])]
    protected ?string $status = null;

    #[SA\Property(
        property: 'tenant',
        ref: '#/components/schemas/jot.shield.entity.user.tenant',
        description: 'The user main tenant',
        x: ['php_type' => '\Jot\HfShield\Entity\User\Tenant']
    )]
    protected ?Tenant $tenant = null;

    protected ?DateTimeInterface $updatedAt = null;

    public function getPasswordSalt(): ?string
    {
        return $this->passwordSalt;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
