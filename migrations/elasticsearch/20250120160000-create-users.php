<?php

use Jot\HfElastic\Migration;
use Jot\HfElastic\Migration\Mapping;

return new class extends Migration {

    public const INDEX_NAME = 'aev1_users';

    public function up(): void
    {
        $index = new Mapping(name: self::INDEX_NAME);

        $index->keyword('id');
        $index->keyword('name')->normalizer('normalizer_ascii_lower');
        $index->keyword('email');
        $index->keyword('phone');
        $index->keyword('picture');
        $index->keyword('privileges');
        $index->keyword('password');

        $profiles = new Migration\ElasticType\NestedType('profiles');
        $profiles->keyword('id');
        $profiles->keyword('name');
        $index->nested($profiles);

        $index->alias('user.identifier')->path('id');
        $index->defaults();

        $index->settings([
            'index' => [
                'number_of_shards' => 3,
                'number_of_replicas' => 1,
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