<?php

declare(strict_types=1);
/**
 * This file is part of hf-shield.
 *
 * @link     https://github.com/JotJunior/hf-shield
 * @contact  hf-shield@jot.com.br
 * @license  MIT
 */
use Hyperf\Context\ApplicationContext;
use Jot\HfElastic\Migration;
use Jot\HfElastic\Migration\Mapping;

return new class(ApplicationContext::getContainer()) extends Migration {
    public const INDEX_NAME = 'refresh_tokens';

    public bool $addPrefix = true;

    public function up(): void
    {
        $index = new Mapping(name: self::INDEX_NAME);

        $index->addField('keyword', 'id');

        $accessToken = new Migration\ElasticType\ObjectType('access_token');
        $accessToken->addField('keyword', 'id');
        $accessToken->addField('date_nanos', 'expiry_date_time');
        $index->object($accessToken);

        $index->addField('date_nanos', 'expiry_date_time');
        $index->alias('refresh_token_id')->path('id');
        $index->alias('access_token_id')->path('access_token.id');
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
