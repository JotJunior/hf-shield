<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Entity\Client;

use Jot\HfRepository\Entity;
use Jot\HfRepository\Trait\HasTimestamps;
use Jot\HfRepository\Trait\HasLogicRemoval;
use Hyperf\Swagger\Annotation as SA;

#[SA\Schema(schema: "app.entity.client.client")]
class Client extends Entity
{

    use HasLogicRemoval, HasTimestamps;

        #[SA\Property(
        property: "client_identifier",
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
        property: "secret",
        type: "string",
        example: ""
    )]
    protected ?string $secret = null;

    #[SA\Property(
        property: "updated_at",
        type: "string",
        format: "string",
        readOnly: true,
        x: ["php_type" => "\DateTime"]
    )]
    protected ?\DateTimeInterface $updatedAt = null;



}
