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
    public const INDEX_NAME = 'access_tokens';

    public bool $addPrefix = true;

    public function mapping(): Mapping
    {
        $index = new Mapping(name: self::INDEX_NAME);

        $index->addField('keyword', 'id');
        $index->addField('date_nanos', 'expiry_date_time');

        $metadata = new Migration\ElasticType\NestedType('metadata');
        $metadata->addField('keyword', 'key');
        $metadata->addField('keyword', 'value');
        $index->nested($metadata);

        $user = new Migration\ElasticType\ObjectType('user');
        $user->addField('keyword', 'id');
        $user->addField('keyword', 'name')->normalizer('normalizer_ascii_lower')->searchable();
        $index->object($user);

        $client = new Migration\ElasticType\ObjectType('client');
        $client->addField('keyword', 'id');
        $client->addField('keyword', 'name')->normalizer('normalizer_ascii_lower')->searchable();
        $client->addField('keyword', 'redirect_uri');
        $index->object($client);

        $tenant = new Migration\ElasticType\ObjectType('tenant');
        $tenant->addField('keyword', 'id');
        $tenant->addField('keyword', 'name');
        $index->object($tenant);

        $index->addField('boolean', 'confidential');

        $scopes = new Migration\ElasticType\NestedType('scopes');
        $scopes->addField('keyword', 'id');
        $scopes->addField('keyword', 'name')->normalizer('normalizer_ascii_lower')->searchable();
        $index->nested($scopes);

        $index->alias('access_token_id')->path('id');
        $index->alias('access_token_identifier')->path('id');
        $index->alias('client_id')->path('client.id');
        $index->alias('client_identifier')->path('client.id');
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
