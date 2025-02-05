<?php

use Hyperf\Context\ApplicationContext;
use Jot\HfElastic\Migration;
use Jot\HfElastic\Migration\Mapping;

return new class(ApplicationContext::getContainer()) extends Migration {

    public const INDEX_NAME = 'users';
    public bool $addPrefix = true;

    public function up(): void
    {
        $index = new Mapping(name: self::INDEX_NAME);

        // basic user data
        $index->keyword('id');
        $index->keyword('name')->normalizer('normalizer_ascii_lower');
        $index->keyword('email');
        $index->keyword('phone');
        $index->keyword('federal_document');
        $index->keyword('picture');
        $index->keyword('password_salt');
        $index->keyword('password');
        $index->keyword('status');

        // user tenant
        $tenant = new Migration\ElasticType\NestedType('tenant');
        $tenant->keyword('id');
        $tenant->keyword('name');
        $index->nested($tenant);

        // oauth client
        $client = new Migration\ElasticType\ObjectType('client');
        $client->keyword('id');
        $client->keyword('name')->normalizer('normalizer_ascii_lower');
        $index->object($client);

        // enabled scopes
        $scopes = new Migration\ElasticType\NestedType('scopes');
        $scopes->keyword('id');
        $scopes->keyword('name')->normalizer('normalizer_ascii_lower');
        $scopes->keyword('tenant_identifier');
        $index->nested($scopes);

        $index->alias('client_identifier')->path('client.id');
        $index->alias('user_identifier')->path('id');
        $index->defaults();

        $index->settings([
            'index' => [
                'number_of_shards' => $this->settings['index']['number_of_shards'],
                'number_of_replicas' => $this->settings['index']['number_of_replicas'],
            ],
            "analysis" => [
                "normalizer" => [
                    "normalizer_ascii_lower" => [
                        "type" => "custom",
                        "char_filter" => [],
                        "filter" => [
                            "asciifolding",
                            "lowercase"
                        ]
                    ]
                ]
            ]
        ]);

        $this->create($index);

    }

    public function down(): void
    {
        $this->delete(self::INDEX_NAME);
    }
};