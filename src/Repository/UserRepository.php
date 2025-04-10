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

use Jot\HfRepository\Entity;
use Jot\HfRepository\Entity\EntityInterface;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use Jot\HfRepository\Exception\RepositoryUpdateException;
use Jot\HfShield\Entity\User\User;
use Jot\HfShield\Entity\UserEntity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

use function Hyperf\Support\make;

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    protected string $entity = User::class;

    /**
     * Retrieves a user entity based on the provided user credentials.
     *
     * @param string $username the username or email of the user attempting to authenticate
     * @param string $password the password provided by the user
     * @param string $grantType the grant type associated with the authentication request
     * @param ClientEntityInterface $clientEntity the client entity requesting the authentication
     *
     * @return null|UserEntityInterface returns a UserEntityInterface instance if the credentials are valid, otherwise null
     */
    public function getUserEntityByUserCredentials(
        string $username,
        string $password,
        string $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface {
        /** @var User $user */
        $user = $this->first(['email' => $username]);
        if (empty($user)) {
            return null;
        }

        $passwordSalt = $user->getPasswordSalt();
        if (! $this->isPasswordValid($user->getPassword(), $password, $passwordSalt)) {
            return null;
        }

        return (new UserEntity())->setIdentifier($user->getId());
    }

    /**
     * Creates a new user entity by validating and processing it, and then inserts it into the database.
     *
     * @param EntityInterface $entity the entity instance to be created
     * @return EntityInterface returns the created user entity instance with the inserted data
     *
     * @throws EntityValidationWithErrorsException
     */
    public function create(EntityInterface $entity): EntityInterface
    {
        $this->validateUser($entity);
        $encryptionKey = $this->config['encryption_key'];
        $entity->addSalt();
        $this->hashUserPassword($entity, $encryptionKey);

        $insertResult = $this->queryBuilder
            ->into($this->index)
            ->insert($entity->toArray());

        return make(User::class, ['data' => $insertResult['data']]);
    }

    /**
     * Updates the scopes of a given user entity by merging the incoming scopes
     * with the existing ones, ensuring uniqueness and maintaining the user state.
     *
     * @param EntityInterface $user the user entity whose scopes need to be updated
     * @param array $scopes the array of new scopes to be added or merged
     * @return EntityInterface the updated user entity with the modified scopes
     *
     * @throws RepositoryUpdateException
     */
    public function updateScopes(EntityInterface $user, string $tenantId, array $scopes): EntityInterface
    {
        $userData = $user->toArray();
        $tenantScopes = [];
        foreach ($userData['tenants'] ?? [] as $tenant) {
            if ($tenant['id'] == $tenantId) {
                $tenantScopes = $tenant['scopes'] ?? [];
            }
        }

        $scopes
            = array_values(
                array_unique(
                    array_merge($scopes, $tenantScopes ?? []),
                    SORT_REGULAR
                )
            );
        $user = make(User::class, ['data' => [
            'id' => $user->getId(),
            'tenants' => [
                ['id' => $tenantId, 'scopes' => $scopes],
            ],
        ]]);
        $user
            ->setEntityState(Entity::STATE_UPDATE)
            ->hide(['password', 'password_salt']);
        return $this->update($user);
    }

    /**
     * Validates if the given plain password matches the hashed password after applying the hash algorithm with the salt.
     *
     * @param string $hashedPassword the hashed password to validate against
     * @param string $plainPassword the plain password input provided by the user
     * @param null|string $passwordSalt the salt used in the password hashing process, or null if none
     *
     * @return bool returns true if the plain password matches the hashed password; otherwise, false
     */
    private function isPasswordValid(string $hashedPassword, string $plainPassword, ?string $passwordSalt): bool
    {
        $encryptionKey = $this->config['encryption_key'];
        $computedHash = hash_hmac('sha256', $plainPassword . $passwordSalt, $encryptionKey);
        return hash_equals($hashedPassword, $computedHash);
    }

    /**
     * Validates the given user entity and ensures it meets the required criteria.
     * If validation fails, an exception containing validation errors will be thrown.
     *
     * @param EntityInterface $user the user entity to be validated
     * @throws EntityValidationWithErrorsException
     */
    private function validateUser(EntityInterface $user): void
    {
        if (! $user->validate()) {
            throw new EntityValidationWithErrorsException($user->getErrors());
        }
    }

    /**
     * Hashes the user's password using the provided encryption key and their password salt.
     *
     * @param EntityInterface $user the user entity containing the password and related properties
     * @param string $encryptionKey the encryption key used for generating the password hash
     */
    private function hashUserPassword(EntityInterface $user, string $encryptionKey): void
    {
        $user->createHash(
            property: 'password',
            salt: $user->getPasswordSalt(),
            encryptionKey: $encryptionKey
        );
    }

}
