<?php

use Hyperf\Context\ApplicationContext;
use Jot\HfElastic\Migration;
use Jot\HfElastic\Migration\Mapping;

return new class(ApplicationContext::getContainer()) extends Migration {

    public const INDEX_NAME = 'clients';
    public bool $addPrefix = true;

    public function up(): void
    {
        $index = new Mapping(name: self::INDEX_NAME);

        $index->keyword('id');
        $index->keyword('name')->normalizer('normalizer_ascii_lower');
        $index->keyword('redirect_uri');
        $index->keyword('secret');
        $index->boolean('confidential');
        $index->alias('client.identifier')->path('id');
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