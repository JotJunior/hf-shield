<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */
use Hyperf\Context\ApplicationContext;
use Jot\HfElastic\Migration;
use Jot\HfElastic\Migration\Mapping;

return new class(ApplicationContext::getContainer()) extends Migration {
    public const INDEX_NAME = 'groups';

    public bool $addPrefix = true;

    public function mapping(): Mapping
    {
        $index = new Mapping(name: self::INDEX_NAME);

        $index->addField('keyword', 'tags')->normalizer('normalizer_ascii_lower')->searchable();

        return $index;
    }

    public function up(): void
    {
        $this->update($this->mapping());
    }
};
