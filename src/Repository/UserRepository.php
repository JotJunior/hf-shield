<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Repository;

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

        $salt = $this->decrypt($user->getPasswordSalt());
        if (!hash_equals($user->getPassword(), hash_hmac('sha256', $password . $salt, $this->config['encryption_key']))) {
            return null;
        }

        return make($this->entity, [$user->toArray()]);
    }
}
