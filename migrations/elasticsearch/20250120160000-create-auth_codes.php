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
    public const INDEX_NAME = 'auth_codes';

    public bool $addPrefix = true;

    public function mapping(): Mapping
    {
        $index = new Mapping(name: self::INDEX_NAME);

        $index->addField('keyword', 'id');

        $user = new Migration\ElasticType\ObjectType('user');
        $user->addField('keyword', 'id');
        $user->addField('keyword', 'name')->normalizer('normalizer_ascii_lower')->searchable();
        $index->object($user);

        $client = new Migration\ElasticType\ObjectType('client');
        $client->addField('keyword', 'id');
        $client->addField('keyword', 'name')->normalizer('normalizer_ascii_lower')->searchable();
        $index->object($client);

        $index->addField('date_nanos', 'expiry_date_time');
        $index->alias('auth_code_id')->path('id');
        $index->alias('client_id')->path('client.id');
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
