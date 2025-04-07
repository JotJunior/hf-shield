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
    public const INDEX_NAME = 'clients';

    public bool $addPrefix = true;

    public function up(): void
    {
        $index = new Mapping(name: self::INDEX_NAME);

        $index->addField('keyword', 'id');
        $index->addField('keyword', 'name')->normalizer('normalizer_ascii_lower');
        $index->addField('keyword', 'redirect_uri');
        $index->addField('keyword', 'secret');
        $index->addField('boolean', 'confidential');
        $index->addField('keyword', 'status');

        // client attached tenant
        $tenant = new Migration\ElasticType\ObjectType('tenant');
        $tenant->addField('keyword', 'id');
        $tenant->addField('keyword', 'name');
        $index->object($tenant);

        // enabled scopes
        $scopes = new Migration\ElasticType\NestedType('scopes');
        $scopes->addField('keyword', 'id');
        $scopes->addField('keyword', 'name')->normalizer('normalizer_ascii_lower');
        $index->nested($scopes);

        $index->alias('client_id')->path('id');
        $index->alias('tenant_id')->path('tenant.id');
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

        $this->create($index);
    }

    public function down(): void
    {
        $this->delete(self::INDEX_NAME);
    }
};
