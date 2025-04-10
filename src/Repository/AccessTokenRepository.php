<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Repository;

use Hyperf\Cache\Annotation\Cacheable;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Jot\HfRepository\Exception\RepositoryCreateException;
use Jot\HfShield\Dto\OAuth\User\UserSessionDto;
use Jot\HfShield\Entity\AccessToken\AccessToken;
use Jot\HfShield\Entity\AccessTokenEntity;
use Jot\HfShield\Entity\Tenant\Tenant;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use ReflectionException;

use function Hyperf\Support\make;

class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
{
    protected string $entity = AccessToken::class;

    protected ?Tenant $tenant = null;

    /**
     * Persists a new access token entity into the storage.
     *
     * @param AccessTokenEntityInterface $accessTokenEntity the access token entity instance containing the token details to persist
     * @throws ReflectionException
     * @throws EntityValidationWithErrorsException
     * @throws RepositoryCreateException
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $entity = make($this->entity, ['data' => [
            'id' => $accessTokenEntity->getIdentifier(),
            'client' => [
                'id' => $accessTokenEntity->getClient()->getIdentifier(),
                'name' => $accessTokenEntity->getClient()->getName(),
                'redirect_uri' => $accessTokenEntity->getClient()->getRedirectUri(),
            ],
            'user' => [
                'id' => $accessTokenEntity->getUserIdentifier(),
            ],
            'scopes' => array_map(fn ($scope) => ['id' => $scope->getIdentifier()], $accessTokenEntity->getScopes()),
            'tenant' => [
                'id' => $accessTokenEntity->getClient()?->getTenantId(),
            ],
            'expiry_date_time' => $accessTokenEntity->getExpiryDateTime(),
        ]]);
        $this->create($entity);
    }

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function setTenant(?Tenant $tenant): AccessTokenRepository
    {
        $this->tenant = $tenant;
        return $this;
    }

    /**
     * Revokes an access token by its unique identifier.
     *
     * @param mixed $tokenId the unique identifier of the access token to be revoked
     */
    public function revokeAccessToken($tokenId): void
    {
        $this->delete($tokenId);
    }

    /**
     * Checks if an access token has been revoked based on the provided token ID.
     *
     * @param mixed $tokenId the unique identifier of the access token to check
     * @return bool returns true if the token has been revoked, false otherwise
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        return ! $this->exists($tokenId);
    }

    /**
     * Generates a new access token for the specified client entity, user, and scopes.
     *
     * @param ClientEntityInterface $clientEntity the client entity requesting the token
     * @param array $scopes the list of scopes to associate with the token
     * @param null|string $userIdentifier the unique identifier of the user, or null if not applicable
     * @return AccessTokenEntityInterface the newly created access token entity
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenEntityInterface
    {
        $newToken = new AccessTokenEntity();
        $newToken->setClient($clientEntity);
        $newToken->setUserIdentifier($userIdentifier);
        foreach ($scopes as $scope) {
            $newToken->addScope($scope);
        }
        return $newToken;
    }

    /**
     * Validates if a user is valid based on provided user ID, tenant ID, and scope.
     *
     * @param string $userId the unique identifier of the user
     * @param string $tenantId the identifier of the tenant associated with the user
     * @param array|string $scope the scope or permissions required to validate the user
     * @return bool returns true if the user is valid, false otherwise
     * @throws ReflectionException
     */
    public function isUserValid(string $userId, string $tenantId, array|string $scope): bool
    {
        $query = $this->queryBuilder
            ->from('users')
            ->where('id', $userId)
            ->where('status', 'active');

        $this->addScopeConditions($query, $tenantId, $scope);

        return $query->count() === 1;
    }

    /**
     * Retrieves a list of tenants associated with a specific user.
     *
     * @param string $userId the unique identifier of the user
     * @return array an array containing the tenants associated with the user
     * @throws ReflectionException
     */
    public function getUserTenants(string $userId): array
    {
        return $this->queryBuilder
            ->select('tenants')
            ->from('users')
            ->where('id', $userId)
            ->andWhere('deleted', false)
            ->execute();
    }

    /**
     * Retrieves the session data for a user based on the given user ID.
     *
     * @param string $id the unique identifier of the user
     * @return array an associative array containing the user's session data
     */
    #[Cacheable(prefix: 'user:session')]
    public function getUserSessionData(string $id): array
    {
        $user = $this->queryBuilder
            ->from('users')
            ->where('id', $id)
            ->andWhere('deleted', false)
            ->execute();

        return make(
            UserSessionDto::class,
            ['data' => $user['data'][0] ?? []]
        )->toArray();
    }

    /**
     * Validates whether the given client ID corresponds to an active client.
     *
     * @param string $clientId the unique identifier of the client
     * @return null|array client data if the client is valid and active, null otherwise
     */
    public function isClientValid(string $clientId): ?array
    {
        return current(
            $this->queryBuilder
                ->select(['id', 'name', 'tenant'])
                ->from('clients')
                ->where('id', $clientId)
                ->where('status', 'active')
                ->execute()['data']
        );
    }

    /**
     * Adds scope conditions to a query based on tenant ID and scope(s).
     *
     * @param mixed $query the query object to which scope conditions will be added
     * @param string $tenantId the ID of the tenant to be included in the query conditions
     * @param array|string $scope The scope(s) used to filter the query. Can be a single scope or an array of scopes.
     */
    private function addScopeConditions($query, string $tenantId, array|string $scope): void
    {
        $isArrayScope = is_array($scope);

        if ($isArrayScope) {
            foreach ($scope as $item) {
                $query->whereNested(
                    'tenants',
                    fn ($query) => $query
                        ->where('tenants.id', $tenantId)
                        ->whereNested('tenants.scopes', fn ($query) => $query->where('tenants.scopes.id', $item))
                );
            }
        } else {
            $query->whereNested(
                'tenants',
                fn ($query) => $query
                    ->where('tenants.id', $tenantId)
                    ->whereNested('tenants.scopes', fn ($query) => $query->where('tenants.scopes.id', $scope))
            );
        }
    }
}
