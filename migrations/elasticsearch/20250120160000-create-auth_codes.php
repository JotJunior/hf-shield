<?php

use Hyperf\Context\ApplicationContext;
use Jot\HfElastic\Migration;
use Jot\HfElastic\Migration\Mapping;

return new class(ApplicationContext::getContainer()) extends Migration {

    public const INDEX_NAME = 'auth_codes';
    public bool $addPrefix = true;

    public function up(): void
    {
        $index = new Mapping(name: self::INDEX_NAME);

        $index->keyword('id');

        $user = new Migration\ElasticType\ObjectType('user');
        $user->keyword('id');
        $user->keyword('name')->normalizer('normalizer_ascii_lower');
        $index->object($user);

        $client = new Migration\ElasticType\ObjectType('client');
        $client->keyword('id');
        $client->keyword('name')->normalizer('normalizer_ascii_lower');
        $index->object($client);

        $index->dateNanos('expiry_date_time');
        $index->alias('auth_code_identifier')->path('id');
        $index->alias('client_identifier')->path('client.id');
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