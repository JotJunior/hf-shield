<?php

declare(strict_types=1);

namespace Jot\HfShield\Entity\Tenant;

use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;
use Jot\HfRepository\Entity\Traits\HasLogicRemovalTrait as HasLogicRemoval;
use Jot\HfRepository\Entity\Traits\HasTimestampsTrait as HasTimestamps;

#[SA\Schema(schema: "jot.shield.entity.tenant.tenant")]
class Tenant extends Entity
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
        property: "domain",
        type: "array",
        example: ""
    )]
    protected null|string|array $domains = null;

    #[SA\Property(
        property: "id",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: "ips",
        type: "array",
        example: ""
    )]
    protected null|string|array $ips = null;

    #[SA\Property(
        property: "name",
        type: "string",
        example: ""
    )]
    protected ?string $name = null;

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


}
