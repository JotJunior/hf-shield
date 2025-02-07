<?php

declare(strict_types=1);

namespace Jot\HfShield\Entity\AuthCode;

use Jot\HfRepository\Entity;
use Jot\HfRepository\Trait\HasTimestamps;
use Jot\HfRepository\Trait\HasLogicRemoval;
use Hyperf\Swagger\Annotation as SA;

#[SA\Schema(schema: "jot.hfshield.entity.authcode.auth_code")]
class AuthCode extends Entity
{

    use HasLogicRemoval, HasTimestamps;

    #[SA\Property(
        property: "access_token",
        ref: "#/components/schemas/jot.hfshield.entity.authcode.accesstoken",
        x: ["php_type" => "\Jot\HfShield\Entity\AuthCode\AccessToken"]
    )]
    protected ?\Jot\HfShield\Entity\AuthCode\AccessToken $accessToken = null;

    #[SA\Property(
        property: "access_token_identifier",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $accessTokenIdentifier = null;

    #[SA\Property(
        property: "auth_code_identifier",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $authCodeIdentifier = null;

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
        property: "expiry_date_time",
        type: "string",
        format: "string",
        x: ["php_type" => "\DateTimeImmutable"]
    )]
    protected ?\DateTimeInterface $expiryDateTime = null;

    #[SA\Property(
        property: "id",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $id = null;

    #[SA\Property(
        property: "updated_at",
        type: "string",
        format: "string",
        readOnly: true,
        x: ["php_type" => "\DateTime"]
    )]
    protected ?\DateTimeInterface $updatedAt = null;


}
