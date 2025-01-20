<?php

/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

declare(strict_types=1);

namespace Jot\HfOAuth2\Entity;

use Jot\HfRepository\Entity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClientEntity extends Entity implements ClientEntityInterface
{
    use EntityTrait;
    use ClientTrait;

    protected ?string $secret = null;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setRedirectUri(string $uri): void
    {
        $this->redirectUri = $uri;
    }

    public function setConfidential(): void
    {
        $this->isConfidential = true;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): ClientEntity
    {
        $this->secret = $secret;
        return $this;
    }


}
