<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
 * @license  MIT
 */

namespace Jot\HfShield\Repository;

use Hyperf\Stringable\Str;
use Jot\HfRepository\EntityInterface;
use Jot\HfShield\Entity\JwtSignature\JwtSignature;
use League\OAuth2\Server\CryptTrait;

use function Hyperf\Support\make;

class JwtSignatureRepository extends AbstractRepository
{
    use CryptTrait;

    protected string $entity = JwtSignature::class;

    public function createJwtSignature(EntityInterface $entity): array
    {
        $data = $entity->toArray();
        $plainHmac = hash_hmac('SHA256', Str::uuid()->toString(), $data['user']['id']);
        $data['hmac'] = $this->encrypt($plainHmac);
        $entity = make($this->entity, ['data' => $data]);

        return [$plainHmac, parent::create($entity)];
    }
}
