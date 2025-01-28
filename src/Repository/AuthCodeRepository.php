<?php

declare(strict_types=1);

namespace Jot\HfOAuth2\Repository;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Jot\HfOAuth2\Entity\AuthCodeEntity;
use function Hyperf\Support\make;

class AuthCodeRepository extends AbstractRepository implements AuthCodeRepositoryInterface
{

    protected string $entity = AuthCodeEntity::class;

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $this->create($authCodeEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId): void
    {
        $this->delete($codeId);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        return !$this->exists($codeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return make(AuthCodeEntity::class, []);
    }
}
