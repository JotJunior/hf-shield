<?php

use Hyperf\Context\ApplicationContext;
use Jot\HfElastic\Migration;
use Jot\HfElastic\Migration\Mapping;

return new class(ApplicationContext::getContainer()) extends Migration {

    public const INDEX_NAME = 'jwt_signatures';
    public bool $addPrefix = true;

    public function up(): void
    {
        $index = new Mapping(name: self::INDEX_NAME);

        $index->keyword('id');
        $index->keyword('name')->normalizer('normalizer_ascii_lower');
        $index->keyword('hmac');
        $index->keyword('status');

        $tenant = new Migration\ElasticType\ObjectType('tenant');
        $tenant->keyword('id');
        $tenant->keyword('name');
        $index->object($tenant);

        $client = new Migration\ElasticType\ObjectType('client');
        $client->keyword('id');
        $client->keyword('name');
        $index->object($client);

        $user = new Migration\ElasticType\ObjectType('user');
        $user->keyword('id');
        $user->keyword('name');
        $index->object($user);

        $scopes = new Migration\ElasticType\NestedType('scopes');
        $scopes->keyword('id');
        $scopes->keyword('name')->normalizer('normalizer_ascii_lower');
        $scopes->keyword('tenant_identifier');
        $index->nested($scopes);

        $index->alias('jwt_signature_identifier')->path('id');
        $index->alias('client_identifier')->path('client.id');
        $index->alias('tenant_identifier')->path('tenant.id');
        $index->alias('user_identifier')->path('user.id');
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