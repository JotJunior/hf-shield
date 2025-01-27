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
        $index->keyword('picture');
        $index->keyword('privileges');
        $index->keyword('password_salt');
        $index->keyword('password');

        // attached profiles
        $profiles = new Migration\ElasticType\NestedType('profiles');
        $profiles->keyword('id');
        $profiles->keyword('name');
        $index->nested($profiles);

        // oauth client
        $client = new Migration\ElasticType\ObjectType('client');
        $client->keyword('id');
        $client->keyword('name')->normalizer('normalizer_ascii_lower');
        $index->object($client);

        // user allowed scopes
        $index->keyword('scopes');

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