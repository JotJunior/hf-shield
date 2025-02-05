<?php

declare(strict_types=1);

namespace Jot\HfShield\Entity\AccessToken;

use Jot\HfRepository\Entity;
use Hyperf\Swagger\Annotation as SA;

#[SA\Schema(schema: "jot.shield.entity.accesstoken.tenant")]
class Tenant extends Entity
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


}
