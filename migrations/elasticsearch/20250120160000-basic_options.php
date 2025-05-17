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
use Jot\HfElastic\Migration\ElasticType\NestedType;
use Jot\HfElastic\Migration\Mapping;

return new class(ApplicationContext::getContainer()) extends Migration {
    public const INDEX_NAME = 'basic_options';

    public bool $addPrefix = true;

    public function mapping(): Mapping
    {
        $index = new Mapping(name: self::INDEX_NAME, dynamic: 'strict');

        $index->addField('keyword', 'id');
        $index->addField('keyword', 'parent_id');
        $index->addField('keyword', 'name')->normalizer('normalizer_ascii_lower')->searchable();
        $index->addField('keyword', 'description');
        $index->addField('keyword', 'value');
        $index->addField('keyword', 'default_value');

        $options = new NestedType('options');
        $options->addField('keyword', 'key');
        $options->addField('keyword', 'value');
        $options->addField('keyword', 'translation_key');
        $options->addField('text', 'extra');
        $index->nested($options);

        $index->addField('keyword', 'domain');
        $index->addField('keyword', 'icon');
        $index->addField('keyword', 'status');
        $index->addField('boolean', 'read_only');

        $index->alias('basic_options_id')->path('id');

        $tenant = new Migration\ElasticType\ObjectType('tenant');
        $tenant->addField('keyword', 'id');
        $tenant->addField('keyword', 'name')->normalizer('normalizer_ascii_lower');
        $index->object($tenant);

        $index->defaults();

        $index->settings(
            [
                'index' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'refresh_interval' => '1s',
                ],
                'analysis' => [
                    'normalizer' => [
                        'normalizer_ascii_lower' => [
                            'type' => 'custom',
                            'char_filter' => [
                            ],
                            'filter' => [
                                0 => 'asciifolding',
                                1 => 'lowercase',
                            ],
                        ],
                    ],
                ],
            ]
        );

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
