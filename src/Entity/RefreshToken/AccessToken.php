<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Entity\RefreshToken;

use Jot\HfRepository\Entity;
use Jot\HfRepository\Trait\HasTimestamps;
use Jot\HfRepository\Trait\HasLogicRemoval;
use Hyperf\Swagger\Annotation as SA;

#[SA\Schema(schema: "jot.hfoauth2.entity.refreshtoken.access_token")]
class AccessToken extends Entity
{


    #[SA\Property(
        property: "expiry_date_time",
        type: "string",
        format: "string",
        x: ["php_type" => "\DateTimeImmutable"]
    )]
    protected ?\DateTimeInterface $expiryDateTime = null;

    #[SA\Property(
        property: "id",
        type: "string",
        example: ""
    )]
    protected ?string $id = null;


}
