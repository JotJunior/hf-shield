<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Entity\AccessToken;

use Jot\HfRepository\Entity;
use Jot\HfRepository\Trait\HasTimestamps;
use Jot\HfRepository\Trait\HasLogicRemoval;
use Hyperf\Swagger\Annotation as SA;

#[SA\Schema(schema: "app.entity.accesstoken.access_token")]
class AccessToken extends Entity
{

    use HasLogicRemoval, HasTimestamps;

        #[SA\Property(
        property: "access_token_identifier",
        type: "string",
        readOnly: true,
        example: ""
    )]
    protected ?string $accessTokenIdentifier = null;

    #[SA\Property(
        property: "client",
        ref: "#/components/schemas/jot.hfoauth2.entity.accesstoken.client",
        x: ["php_type" => "\Jot\HfOAuth2\Entity\AccessToken\Client"]
    )]
    protected ?\Jot\HfOAuth2\Entity\AccessToken\Client $client = null;

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
        property: "scopes",
        type: "array",
        items: new SA\Items(ref: "#/components/schemas/jot.hfoauth2.entity.accesstoken.scope"),
        x: ["php_type" => "\Jot\HfOAuth2\Entity\AccessToken\Scope[]"]
    )]
    protected ?array $scopes = null;

    #[SA\Property(
        property: "updated_at",
        type: "string",
        format: "string",
        readOnly: true,
        x: ["php_type" => "\DateTime"]
    )]
    protected ?\DateTimeInterface $updatedAt = null;

    #[SA\Property(
        property: "user",
        ref: "#/components/schemas/jot.hfoauth2.entity.accesstoken.user",
        x: ["php_type" => "\Jot\HfOAuth2\Entity\AccessToken\User"]
    )]
    protected ?\Jot\HfOAuth2\Entity\AccessToken\User $user = null;



}
