<?php

declare(strict_types=1);

namespace Jot\HfShield\Entity\User;

use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;

#[SA\Schema(schema: "jot.hf-shield.entity.user.scope")]
class Scope extends Entity
{


    #[SA\Property(
        property: "id",
        type: "string",
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
        property: "tenant_identifier",
        description: "An alias of tenant id",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $tenantIdentifier = null;


}
