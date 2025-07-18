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
use Hyperf\Di\Annotation\Inject;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Jot\HfRepository\Exception\RepositoryCreateException;
use Jot\HfShield\Dto\OAuth\User\UserSessionDto;
use Jot\HfShield\Entity\AccessToken\AccessToken;
use Jot\HfShield\Entity\AccessTokenEntity;
use Jot\HfShield\Entity\Tenant\Tenant;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

use function Hyperf\Support\make;

class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
{
    protected string $entity = AccessToken::class;

    protected ?Tenant $tenant = null;

    #[Inject]
    protected ServerRequestInterface $request;

    /**
     * Persists a new access token entity into the storage.
     * @param AccessTokenEntityInterface $accessTokenEntity the access token entity instance containing the token details to persist
     * @throws ReflectionException
     * @throws EntityValidationWithErrorsException
     * @throws RepositoryCreateException
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $metadata = $this->collectMetadata();

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
            'metadata' => $metadata,
            'expiry_date_time' => $accessTokenEntity->getExpiryDateTime(),
        ]]);
        $this->create($entity);
        $this->syncUserScopeList(
            userId: $accessTokenEntity->getUserIdentifier(),
            tenantId: $accessTokenEntity->getClient()?->getTenantId()
        );
    }

    /**
     * Collects metadata from server parameters and request headers.
     * @return array returns an array of metadata, each containing a key-value pair for specific server or header information
     */
    public function collectMetadata(): array
    {
        $serverParams = $this->request->getServerParams();
        $headers = $this->request->getHeaders();
        return [
            ['key' => 'path_info', 'value' => $serverParams['path_info'] ?? null],
            ['key' => 'remote_addr', 'value' => $serverParams['remote_addr'] ?? null],
            ['key' => 'remote_port', 'value' => $serverParams['remote_port'] ?? null],
            ['key' => 'real_ip', 'value' => current($headers['x-real-ip'] ?? [])],
            ['key' => 'user_agent', 'value' => current($headers['user-agent'] ?? [])],
        ];
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
     * @param mixed $tokenId the unique identifier of the access token to be revoked
     */
    public function revokeAccessToken($tokenId): void
    {
        $this->delete($tokenId);
    }

    /**
     * Checks if an access token has been revoked based on the provided token ID.
     * @param mixed $tokenId the unique identifier of the access token to check
     * @return bool returns true if the token has been revoked, false otherwise
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        return ! $this->exists($tokenId);
    }

    /**
     * Generates a new access token for the specified client entity, user, and scopes.
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
     * @param string $userId the unique identifier of the user
     * @param string $tenantId the identifier of the tenant associated with the user
     * @param array|string $scope the scope or permissions required to validate the user
     * @return bool returns true if the user is valid, false otherwise
     * @throws ReflectionException
     */
    #[Cacheable(prefix: 'scope-validated-user', ttl: 120)]
    public function isUserValid(string $userId, string $tenantId, array|string $scope): bool
    {
        $query = $this->queryBuilder
            ->from('users')
            ->where('id', $userId)
            ->andWhere('deleted', false)
            ->andWhere('status', 'active');

        $this->addScopeConditions($query, $tenantId, $scope);

        return boolval($query->count());
    }

    /**
     * Retrieves a list of tenants associated with a specific user.
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
            ->andWhere('status', 'active')
            ->execute();
    }

    /**
     * Retrieves the session data for a user based on the given user ID.
     * @param string $id the unique identifier of the user
     * @return array an associative array containing the user's session data
     * @throws ReflectionException
     */
    #[Cacheable(prefix: 'user-session', ttl: 120)]
    public function getUserSessionData(string $id): array
    {
        $user = $this->queryBuilder
            ->select()
            ->from('users')
            ->where('id', '=', $id)
            ->andWhere('status', 'active')
            ->andWhere('deleted', false)
            ->execute();

        return make(
            UserSessionDto::class,
            ['data' => $user['data'][0] ?? []]
        )->toArray();
    }

    /**
     * Validates whether the given client ID corresponds to an active client.
     * @param string $clientId the unique identifier of the client
     * @return null|array client data if the client is valid and active, null otherwise
     */
    #[Cacheable(prefix: 'oauth:client', ttl: 84600)]
    public function isClientValid(string $clientId): ?array
    {
        return current(
            $this->queryBuilder
                ->select(['id', 'name', 'tenant'])
                ->from('clients')
                ->where('id', $clientId)
                ->andWhere('status', 'active')
                ->andWhere('deleted', false)
                ->execute()['data']
        );
    }

    /**
     * Retrieves a list of logs associated with a specific user, ordered by creation date in descending order.
     * @param string $userId the unique identifier of the user
     * @return array returns an array of logs for the specified user
     */
    public function getUserLogList(string $userId): array
    {
        return $this->queryBuilder->select()
            ->from($this->index)
            ->where('user.id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->execute();
    }

    private function syncUserScopeList(string $userId, string $tenantId): void
    {
        $user = current($this->queryBuilder
            ->select()
            ->from('users')
            ->where('id', $userId)
            ->andWhere('deleted', false)
            ->andWhere('status', 'active')
            ->execute()['data']);

        foreach ($user['tenants'] as &$tenant) {
            $scopes = [];
            $rootScopes = current(array_filter($tenant['scopes'] ?? [], function ($scope) {
                return $scope['id'] === 'root:all:all';
            }));

            $validGroups = [];

            foreach ($tenant['groups'] ?? [] as $group) {
                $group = $this->queryBuilder
                    ->select(['scopes'])
                    ->from('groups')
                    ->where('id', $group['id'])
                    ->andWhere('deleted', false)
                    ->andWhere('status', 'active')
                    ->execute()['data'];
                if (empty($group[0])) {
                    continue;
                }
                $validGroups[] = $group;
                $scopes = array_merge($scopes, $group[0]['scopes']);
            }
            $scopes = array_unique($scopes, SORT_REGULAR);
            if ($rootScopes) {
                $scopes[] = $rootScopes;
            }
            $tenant['scopes'] = $scopes;
            $tenant['groups'] = $validGroups;
        }

        $this->queryBuilder->update($userId, $user);
    }

    /**
     * Adds scope conditions to a query based on tenant ID and scope(s).
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
                        ->whereNested(
                            'tenants.scopes',
                            fn ($query) => $query->orWhere('tenants.scopes.id', $item)
                                ->orWhere('tenants.scopes.id', 'root:all:all')
                        )
                );
            }
        } else {
            $query->whereNested(
                'tenants',
                fn ($query) => $query
                    ->where('tenants.id', $tenantId)
                    ->whereNested(
                        'tenants.scopes',
                        fn ($query) => $query->orWhere('tenants.scopes.id', $scope)
                            ->orWhere('tenants.scopes.id', 'root:all:all')
                    )
            );
        }
    }
}
