<?php

declare(strict_types=1);

namespace Jot\HfShield\Entity\JwtSignature;

use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfRepository\Trait\HasLogicRemoval;
use Jot\HfRepository\Trait\HasTimestamps;
use Jot\HfValidator\Annotation as Validator;

#[SA\Schema(schema: "jot.hf-shield.entity.jwt_signature.jwt_signature")]
class JwtSignature extends Entity
{

    use HasLogicRemoval, HasTimestamps;

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
        property: "hmac",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $hmac = null;

    #[SA\Property(
        property: "id",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: "jwt_signature_identifier",
        description: "An alias of jwt id",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $jwtSignatureIdentifier = null;

    #[SA\Property(
        property: "name",
        type: "string",
        example: ""
    )]
    #[Validator\Required(onCreate: true, onUpdate: false)]
    protected ?string $name = null;

    #[SA\Property(
        property: "scopes",
        type: "array",
        items: new SA\Items(ref: "#/components/schemas/jot.hf-shield.entity.jwt_signature.scope"),
        x: ["php_type" => "\App\Entity\JwtSignature\Scope[]"]
    )]
    #[Validator\Exists(index: 'scopes', field: 'id')]
    protected ?array $scopes = null;

    #[SA\Property(
        property: "status",
        type: "string",
        enum: ["active", "inactive"],
        example: ""
    )]
    #[Validator\Required(onCreate: true, onUpdate: false)]
    #[Validator\Enum(values: ["active", "inactive"])]
    protected ?string $status = null;

    #[SA\Property(
        property: "tenant",
        ref: "#/components/schemas/jot.hf-shield.entity.jwt_signature.tenant",
        x: ["php_type" => "\App\Entity\JwtSignature\Tenant"]
    )]
    #[Validator\Required(onCreate: true, onUpdate: false)]
    #[Validator\Exists(index: 'tenants', field: 'id')]
    protected ?\Jot\HfShield\Entity\JwtSignature\Tenant $tenant = null;

    #[SA\Property(
        property: "client",
        ref: "#/components/schemas/jot.hf-shield.entity.jwt_signature.client",
        x: ["php_type" => "\App\Entity\JwtSignature\Client"]
    )]
    #[Validator\Required(onCreate: true, onUpdate: false)]
    #[Validator\Exists(index: 'clients', field: 'id')]
    protected ?\Jot\HfShield\Entity\JwtSignature\Client $client = null;

    #[SA\Property(
        property: "tenant_identifier",
        description: "An alias of tenant id",
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
        property: "user",
        ref: "#/components/schemas/jot.hf-shield.entity.jwt_signature.user",
        x: ["php_type" => "\App\Entity\JwtSignature\User"]
    )]
    #[Validator\Required(onCreate: true, onUpdate: false)]
    #[Validator\Exists(index: 'users', field: 'id')]
    protected ?\Jot\HfShield\Entity\JwtSignature\User $user = null;

    #[SA\Property(
        property: "user_identifier",
        description: "An alias of user id",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $userIdentifier = null;


}
