<?php

declare(strict_types=1);

namespace Jot\HfShield\Entity\JwtSignature;

use Hyperf\Swagger\Annotation as SA;
use Jot\HfRepository\Entity;

#[SA\Schema(schema: "jot.hf-shield.entity.jwt_signature.user")]
class User extends Entity
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
