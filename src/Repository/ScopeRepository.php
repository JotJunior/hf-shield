<?php

/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

declare(strict_types=1);

namespace Jot\HfOAuth2\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Jot\HfOAuth2\Entity\ScopeEntity;

use function array_key_exists;

class ScopeRepository extends AbstractRepository implements ScopeRepositoryInterface
{
    public function getScopeEntityByIdentifier(string $identifier): ?ScopeEntityInterface
    {
        $scope = $this->find($identifier);
        if (empty($scope)) {
            return null;
        }

        $scope = new ScopeEntity();
        $scope->setIdentifier($scope->getId());

        return $scope;
    }

    public function finalizeScopes(
        array                 $scopes,
        string                $grantType,
        ClientEntityInterface $clientEntity,
        string|null           $userIdentifier = null,
        ?string               $authCodeId = null
    ): array
    {
        // Example of programatically modifying the final scope of the access token
        if ((int)$userIdentifier === 1) {
            $scope = new ScopeEntity();
            $scope->setIdentifier('email');
            $scopes[] = $scope;
        }

        return $scopes;
    }
}
