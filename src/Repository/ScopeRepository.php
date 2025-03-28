<?php

declare(strict_types=1);

namespace Jot\HfShield\Repository;

use Jot\HfRepository\Entity\EntityInterface;
use Jot\HfShield\Entity\Scope\Scope;
use Jot\HfShield\Entity\ScopeEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use function Hyperf\Support\make;

class ScopeRepository extends AbstractRepository implements ScopeRepositoryInterface
{
    protected string $entity = Scope::class;

    public function getScopeEntityByIdentifier(string $identifier): ?ScopeEntityInterface
    {
        /** @var Scope $scope */
        $scope = $this->find($identifier);
        if (empty($scope)) {
            return null;
        }

        return (make(ScopeEntity::class))->setIdentifier($scope->getId());
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
