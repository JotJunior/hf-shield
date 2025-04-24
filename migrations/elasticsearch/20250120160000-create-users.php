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
    public const INDEX_NAME = 'users';

    public bool $addPrefix = true;

    public function mapping(): Mapping
    {
        $index = new Mapping(name: self::INDEX_NAME);

        // basic user data
        $index->addField('keyword', 'id');
        $index->addField('keyword', 'name')->normalizer('normalizer_ascii_lower')->searchable();
        $index->addField('keyword', 'email');
        $index->addField('keyword', 'phone');
        $index->addField('keyword', 'federal_document');
        $index->addField('keyword', 'document_type');
        $index->addField('keyword', 'picture');
        $index->addField('keyword', 'password_salt');
        $index->addField('keyword', 'password');
        $index->addField('keyword', 'status');
        $index->addField('text', 'custom_settings');

        $tenant = new Migration\ElasticType\NestedType('tenants');
        $tenant->addField('keyword', 'id');
        $tenant->addField('keyword', 'name');

        $scopes = new Migration\ElasticType\NestedType('scopes');
        $scopes->addField('keyword', 'id');
        $scopes->addField('keyword', 'name')->normalizer('normalizer_ascii_lower');
        $scopes->addField('keyword', 'domain');
        $scopes->addField('keyword', 'resource');
        $scopes->addField('keyword', 'action');
        $tenant->nested($scopes);

        $groups = new Migration\ElasticType\NestedType('groups');
        $groups->addField('keyword', 'id');
        $groups->addField('keyword', 'name')->normalizer('normalizer_ascii_lower');
        $tenant->nested($groups);

        $index->nested($tenant);

        $index->alias('user_id')->path('id');
        $index->defaults();

        $index->settings([
            'index' => [
                'number_of_shards' => $this->settings['index']['number_of_shards'],
                'number_of_replicas' => $this->settings['index']['number_of_replicas'],
            ],
            'analysis' => [
                'normalizer' => [
                    'normalizer_ascii_lower' => [
                        'type' => 'custom',
                        'char_filter' => [],
                        'filter' => [
                            'asciifolding',
                            'lowercase',
                        ],
                    ],
                ],
            ],
        ]);

        return $index;
    }

    public function up(): void
    {
        $this->create($this->mapping());
    }

    public function down(): void
    {
        $this->delete(self::INDEX_NAME);
    }
};
