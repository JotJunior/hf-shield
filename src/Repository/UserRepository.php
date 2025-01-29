<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Repository;

use Jot\HfRepository\EntityInterface;
use Jot\HfRepository\Exception\EntityValidationWithErrorsException;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Jot\HfOAuth2\Entity\UserEntity;
use function Hyperf\Support\make;

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    protected string $entity = UserEntity::class;

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?UserEntityInterface
    {
        /** @var UserEntity $user */
        $user = $this->first(['email' => $username]);

        if (empty($user)) {
            return null;
        }

        $salt = $user->getPasswordSalt();
        if (!hash_equals($user->getPassword(), hash_hmac('sha256', $password . $salt, $this->config['encryption_key']))) {
            return null;
        }

        return make($this->entity, [$user->toArray()]);
    }

    public function createUser(UserEntity $user): EntityInterface
    {
        if (!$user->validate()) {
            throw new EntityValidationWithErrorsException($user->getErrors());
        }
        $user->createHash('password', $user->getPasswordSalt(), $this->config['encryption_key']);

        $result = $this->queryBuilder
            ->into($this->index)
            ->insert($user->toArray());

        return make(UserEntity::class, ['data' => $result['data']]);
    }
}
