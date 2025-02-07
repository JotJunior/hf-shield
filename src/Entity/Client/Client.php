<?php

declare(strict_types=1);

namespace Jot\HfShield\Entity\Client;

use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfRepository\Trait\HasLogicRemoval;
use Jot\HfRepository\Trait\HasTimestamps;

#[SA\Schema(schema: "jot.hfshield.entity.client.client")]
class Client extends Entity
{

    use HasLogicRemoval, HasTimestamps;

    #[SA\Property(
        property: "client_identifier",
        description: "An alias of client id",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $clientIdentifier = null;

    #[SA\Property(
        property: "confidential",
        type: "boolean",
        example: true
    )]
    protected ?bool $confidential = null;

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
        property: "redirect_uri",
        type: "string",
        example: ""
    )]
    protected ?string $redirectUri = null;

    #[SA\Property(
        property: "scopes",
        type: "array",
        items: new SA\Items(ref: "#/components/schemas/jot.hfshield.entity.client.scope"),
        x: ["php_type" => "\App\Entity\Client\Scope[]"]
    )]
    protected ?array $scopes = null;

    #[SA\Property(
        property: "secret",
        type: "string",
        example: ""
    )]
    protected ?string $secret = null;

    #[SA\Property(
        property: "status",
        type: "string",
        example: ""
    )]
    protected ?string $status = null;

    #[SA\Property(
        property: "tenant",
        ref: "#/components/schemas/jot.hfshield.entity.client.tenant",
        x: ["php_type" => "\App\Entity\Client\Tenant"]
    )]
    protected ?\App\Entity\Client\Tenant $tenant = null;

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


    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

}
