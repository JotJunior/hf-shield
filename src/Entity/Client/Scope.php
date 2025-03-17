<?php

declare(strict_types=1);

namespace Jot\HfShield\Entity\Client;

use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;

#[SA\Schema(schema: "jot.hf-shield.entity.client.scope")]
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


}
