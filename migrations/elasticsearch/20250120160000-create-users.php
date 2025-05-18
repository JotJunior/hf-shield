<?php

declare(strict_types=1);
/**
 * This file is part of Gekom APIv2.
 *
 * @document https://github.com/JotJunior/gekom
 * @author   Joao Zanon <jot@jot.con.br>
 * @link     https://gekom.com.br
 * @license  Private
 */
use Hyperf\Context\ApplicationContext;
use Jot\HfElastic\Migration;
use Jot\HfElastic\Migration\Mapping;

return new class(ApplicationContext::getContainer()) extends Migration {
    public const INDEX_NAME = 'users';

    public bool $addPrefix = true;

    public function mapping(): Mapping
    {
        $index = new Mapping(name: self::INDEX_NAME);

        // basic user data
        $index->addField('keyword', 'id');
        $index->addField('keyword', 'legacy_id');
        $index->addField('keyword', 'name')->normalizer('normalizer_ascii_lower')->searchable();
        $index->addField('keyword', 'language');
        $index->addField('keyword', 'email')->searchable();
        $index->addField('keyword', 'phone')->searchable();
        $index->addField('keyword', 'federal_document')->searchable();
        $index->addField('keyword', 'document_type');
        $index->addField('keyword', 'picture');
        $index->addField('keyword', 'password_salt');
        $index->addField('keyword', 'password');
        $index->addField('keyword', 'status');
        $index->addField('keyword', 'tags');
        $index->addField('dynamic_object', 'custom_settings');

        $tenant = new Migration\ElasticType\ObjectType('tenant');
        $tenant->addField('keyword', 'id');
        $tenant->addField('keyword', 'name')->normalizer('normalizer_ascii_lower');
        $index->object($tenant);

        $tenants = new Migration\ElasticType\NestedType('tenants');
        $tenants->addField('keyword', 'id');
        $tenants->addField('keyword', 'name')->normalizer('normalizer_ascii_lower');
        $scopes = new Migration\ElasticType\NestedType('scopes');
        $scopes->addField('keyword', 'id');
        $scopes->addField('keyword', 'name')->normalizer('normalizer_ascii_lower');
        $scopes->addField('keyword', 'domain');
        $scopes->addField('keyword', 'resource');
        $scopes->addField('keyword', 'action');
        $tenants->nested($scopes);
        $groups = new Migration\ElasticType\NestedType('groups');
        $groups->addField('keyword', 'id');
        $groups->addField('keyword', 'name')->normalizer('normalizer_ascii_lower');
        $tenants->nested($groups);
        $customers = new Migration\ElasticType\NestedType('customers');
        $customers->addField('keyword', 'id');
        $customers->addField('keyword', 'trade_name')->normalizer('normalizer_ascii_lower');
        $customers->addField('keyword', 'legal_name')->normalizer('normalizer_ascii_lower');
        $tenants->nested($customers);
        $index->nested($tenants);

        $index->alias('user_id')->path('id');
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
