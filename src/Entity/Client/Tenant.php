<?php

declare(strict_types=1);

namespace Jot\HfShield\Entity\Client;

use Jot\HfRepository\Entity;
use Jot\HfRepository\Trait\HasTimestamps;
use Jot\HfRepository\Trait\HasLogicRemoval;
use Hyperf\Swagger\Annotation as SA;

#[SA\Schema(schema: "jot.hfshield.entity.client.tenant")]
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
