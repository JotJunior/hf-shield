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

        $index->addField('keyword', 'id');
        $index->addField('keyword', 'name')->normalizer('normalizer_ascii_lower');
        $index->addField('keyword', 'hmac');
        $index->addField('keyword', 'status');

        $tenant = new Migration\ElasticType\ObjectType('tenant');
        $tenant->addField('keyword', 'id');
        $tenant->addField('keyword', 'name');
        $index->object($tenant);

        $client = new Migration\ElasticType\ObjectType('client');
        $client->addField('keyword', 'id');
        $client->addField('keyword', 'name');
        $index->object($client);

        $user = new Migration\ElasticType\ObjectType('user');
        $user->addField('keyword', 'id');
        $user->addField('keyword', 'name');
        $index->object($user);

        $scopes = new Migration\ElasticType\NestedType('scopes');
        $scopes->addField('keyword', 'id');
        $scopes->addField('keyword', 'name')->normalizer('normalizer_ascii_lower');
        $scopes->addField('keyword', 'tenant_identifier');
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
