<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Service;

use Hyperf\Cache\Annotation\Cacheable;
use Jot\HfRepository\Service\AbstractService;
use Jot\HfShield\Entity\BasicOption\BasicOption as Entity;
use Jot\HfShield\Repository\BasicOptionRepository;
use RuntimeException;

class BasicOptionService extends AbstractService
{
    public const CACHE_PREFIX = 'basic_option:entity';

    protected string $repositoryClass = BasicOptionRepository::class;

    protected string $entityClass = Entity::class;

    /**
     * Retrieves data for a specific entity based on the provided identifier.
     * @param string $id the unique identifier of the entity to retrieve
     * @return array an associative array containing the entity data, a result status, and an error message if applicable
     * @throws RuntimeException
     */
    #[Cacheable(prefix: self::CACHE_PREFIX, ttl: 600, listener: self::CACHE_PREFIX)]
    public function getData(string $id): array
    {
        return parent::getData($id);
    }
}
