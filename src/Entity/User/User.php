<?php

declare(strict_types=1);

namespace Jot\HfShield\Entity\User;

use Hyperf\Stringable\Str;
use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfRepository\Trait\HasLogicRemoval;
use Jot\HfRepository\Trait\HasTimestamps;
use Jot\HfValidator\Annotation as Validator;

#[SA\Schema(schema: "jot.hfshield.entity.user.user", description: "Entity representing a user.")]
class User extends Entity
{
    use HasLogicRemoval, HasTimestamps;

    #[SA\Property(
        property: "client",
        ref: "#/components/schemas/jot.hfshield.entity.user.client",
        description: "Reference to the client associated with the user.",
        x: ["php_type" => "\Jot\HfShield\Entity\User\Client"]
    )]
    #[Validator\Exists(index: 'clients', field: 'id')]
    protected ?\Jot\HfShield\Entity\User\Client $client = null;

    #[SA\Property(
        property: "client_identifier",
        description: "Unique identifier of the client associated with the user.",
        type: "string",
        readOnly: true,
        example: "client_1234"
    )]
    protected ?string $clientIdentifier = null;

    #[SA\Property(
        property: "created_at",
        description: "Timestamp when the user was created.",
        type: "string",
        format: "date-time",
        readOnly: true,
        example: "2023-10-01T12:45:00Z",
        x: ["php_type" => "\DateTime"]
    )]
    protected ?\DateTimeInterface $createdAt = null;

    #[SA\Property(
        property: "deleted",
        description: "Indicates whether the user was logically removed (true or false).",
        type: "boolean",
        readOnly: true,
        example: false
    )]
    protected ?bool $deleted = null;

    #[SA\Property(
        property: "email",
        description: "Unique and valid email address of the user.",
        type: "string",
        example: "user@example.com"
    )]
    #[Validator\Email]
    #[Validator\Unique(index: 'users', field: 'email')]
    #[Validator\Required(onCreate: true, onUpdate: false)]
    protected ?string $email = null;

    #[SA\Property(
        property: "federal_document",
        description: "Federal document (CPF) of the user, unique and valid.",
        type: "string",
        example: "123.456.789-00"
    )]
    #[Validator\CPF]
    #[Validator\Unique(index: 'users', field: 'federal_document')]
    #[Validator\Required(onCreate: true, onUpdate: false)]
    protected ?string $federalDocument = null;

    #[SA\Property(
        property: "id",
        description: "Unique identifier in UUID format for the user.",
        type: "string",
        format: "uuid",
        readOnly: true,
        example: "b3e8b1e4-324d-4909-bb3e-eb4724f5e325"
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: "name",
        description: "Full name of the user.",
        type: "string",
        example: "John Doe"
    )]
    #[Validator\Required(onCreate: true, onUpdate: false)]
    protected ?string $name = null;

    #[SA\Property(
        property: "password",
        description: "Password of the user, stored securely.",
        type: "string",
        example: "strongPassword123!"
    )]
    #[Validator\Password]
    #[Validator\Required(onCreate: true, onUpdate: false)]
    protected ?string $password = null;

    #[SA\Property(
        property: "password_salt",
        description: "Salt used for encrypting the user password, generated automatically.",
        type: "string",
        readOnly: true,
        example: "abcd1234"
    )]
    protected ?string $passwordSalt = null;

    #[SA\Property(
        property: "phone",
        description: "Phone number of the user in the Brazilian standard format.",
        type: "string",
        example: "+55 11 98765-4321"
    )]
    #[Validator\Phone(countryCode: 'BR')]
    #[Validator\Unique(index: 'users', field: 'phone')]
    #[Validator\Required(onCreate: true, onUpdate: false)]
    protected ?string $phone = null;

    #[SA\Property(
        property: "picture",
        description: "URL to the user's profile picture.",
        type: "string",
        example: "https://example.com/images/user.jpg"
    )]
    #[Validator\Url]
    protected ?string $picture = null;

    #[SA\Property(
        property: "scopes",
        description: "Array of scopes defining the user permissions.",
        type: "array",
        items: new SA\Items(ref: "#/components/schemas/jot.hfshield.entity.user.scope"),
        example: [
            ['id' => 'oauth:user:create'],
            ['id' => 'oauth:client:list'],
            ['id' => 'api-events:event:read']
        ],
        x: ["php_type" => "\Jot\HfShield\Entity\User\Scope[]"]
    )]
    protected ?array $scopes = null;

    #[SA\Property(
        property: "status",
        description: "Current status of the user. Can be 'active', 'inactive', or 'pending'.",
        type: "string",
        enum: ["active", "inactive", "pending"],
        example: "active"
    )]
    #[Validator\Enum(values: ["active", "inactive", "pending"])]
    protected ?string $status = null;

    #[SA\Property(
        property: "tenants",
        type: "array",
        items: new SA\Items(ref: "#/components/schemas/jot.hfshield.entity.user.tenant"),
        x: ["php_type" => "\App\Entity\User\Tenant[]"]
    )]
    #[Validator\Exists(index: 'tenants', field: 'id')]
    #[Validator\Required(onCreate: true, onUpdate: false)]
    protected ?array $tenant = null;

    #[SA\Property(
        property: "tenant_identifier",
        description: "Unique identifier of the tenant associated with the user.",
        type: "string",
        readOnly: true,
        example: "tenant_5678"
    )]
    protected ?string $tenantIdentifier = null;

    #[SA\Property(
        property: "updated_at",
        description: "Timestamp of the last update made to the user's data.",
        type: "string",
        format: "date-time",
        readOnly: true,
        example: "2023-10-02T15:00:00Z",
        x: ["php_type" => "\DateTime"]
    )]
    protected ?\DateTimeInterface $updatedAt = null;

    #[SA\Property(
        property: "user_identifier",
        description: "Unique identifier of the user for external systems.",
        type: "string",
        readOnly: true,
        example: "user_9876"
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