<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Entity\User;

use Jot\HfRepository\Entity;
use Jot\HfRepository\Trait\HasTimestamps;
use Jot\HfRepository\Trait\HasLogicRemoval;
use Hyperf\Swagger\Annotation as SA;

#[SA\Schema(schema: "app.entity.user.user")]
class User extends Entity
{

    use HasLogicRemoval, HasTimestamps;

        #[SA\Property(
        property: "client",
        ref: "#/components/schemas/jot.hfoauth2.entity.user.client",
        x: ["php_type" => "\Jot\HfOAuth2\Entity\User\Client"]
    )]
    protected ?\Jot\HfOAuth2\Entity\User\Client $client = null;

    #[SA\Property(
        property: "client_identifier",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $clientIdentifier = null;

    #[SA\Property(
        property: "created_at",
        type: "string",
        format: "string",
        readOnly: true,
        x: ["php_type" => "\DateTime"]
    )]
    protected ?\DateTimeInterface $createdAt = null;

    #[SA\Property(
        property: "deleted",
        type: "boolean",
        readOnly: true,
        example: true
    )]
    protected ?bool $deleted = null;

    #[SA\Property(
        property: "email",
        type: "string",
        example: ""
    )]
    protected ?string $email = null;

    #[SA\Property(
        property: "federal_document",
        type: "string",
        example: ""
    )]
    protected ?string $federalDocument = null;

    #[SA\Property(
        property: "id",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: "name",
        type: "string",
        example: ""
    )]
    protected ?string $name = null;

    #[SA\Property(
        property: "password",
        type: "string",
        example: ""
    )]
    protected ?string $password = null;

    #[SA\Property(
        property: "password_salt",
        type: "string",
        example: ""
    )]
    protected ?string $passwordSalt = null;

    #[SA\Property(
        property: "phone",
        type: "string",
        example: ""
    )]
    protected ?string $phone = null;

    #[SA\Property(
        property: "picture",
        type: "string",
        example: ""
    )]
    protected ?string $picture = null;

    #[SA\Property(
        property: "privilege",
        type: "string",
        example: ""
    )]
    protected ?string $privileges = null;

    #[SA\Property(
        property: "profiles",
        type: "array",
        items: new SA\Items(ref: "#/components/schemas/jot.hfoauth2.entity.user.profile"),
        x: ["php_type" => "\Jot\HfOAuth2\Entity\User\Profile[]"]
    )]
    protected ?array $profiles = null;

    #[SA\Property(
        property: "scope",
        type: "string",
        example: ""
    )]
    protected ?string $scopes = null;

    #[SA\Property(
        property: "tenant",
        ref: "#/components/schemas/jot.hfoauth2.entity.user.tenant",
        x: ["php_type" => "\Jot\HfOAuth2\Entity\User\Tenant"]
    )]
    protected ?\Jot\HfOAuth2\Entity\User\Tenant $tenant = null;

    #[SA\Property(
        property: "tenant_identifier",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $tenantIdentifier = null;

    #[SA\Property(
        property: "updated_at",
        type: "string",
        format: "string",
        readOnly: true,
        x: ["php_type" => "\DateTime"]
    )]
    protected ?\DateTimeInterface $updatedAt = null;

    #[SA\Property(
        property: "user_identifier",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $userIdentifier = null;



}
