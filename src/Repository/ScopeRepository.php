<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Jot\HfOAuth2\Entity\ScopeEntity;

use function array_key_exists;
use function Hyperf\Support\make;

class ScopeRepository extends AbstractRepository implements ScopeRepositoryInterface
{
    protected string $entity = ScopeEntity::class;

    public function getScopeEntityByIdentifier(string $identifier): ?ScopeEntityInterface
    {
        $scope = $this->find($identifier);
        if (empty($scope)) {
            return null;
        }

        return make($this->entity, ['data' => $scope->toArray()]);
    }

    public function finalizeScopes(
        array                 $scopes,
        string                $grantType,
        ClientEntityInterface $clientEntity,
        string|null           $userIdentifier = null,
        ?string               $authCodeId = null
    ): array
    {

        return $scopes;
    }
}
