<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Command;

use Hyperf\Command\Command as HyperfCommand;
use Jot\HfRepository\Command\HfFriendlyLinesTrait;

use function Hyperf\Translation\__;

abstract class AbstractCommand extends HyperfCommand
{
    use HfFriendlyLinesTrait;

    protected function selectClient(string $tenantId): ?array
    {
        return $this->selectItem(
            __('hf-shield.client_list_prompt'),
            __('hf-shield.wrong_client_number'),
            fn () => $this->repository->retrieveClientList($tenantId)['data'] ?? []
        );
    }

    protected function selectItem(string $prompt, string $error, callable $fetchItems): ?array
    {
        $itemInput = $this->ask($prompt);
        if ($itemInput === '-') {
            $items = [];
            foreach ($fetchItems() as $index => $item) {
                $items[] = $item;
                $this->success('<fg=yellow>%d</> - %s : %s', [$index + 1, $item['id'], $item['name']]);
            }
            $pickedNumber = $this->ask(__('hf-shield.pick_a_number') . ': ');
            $selectedItem = $items[(int) $pickedNumber - 1] ?? null;

            if (! $selectedItem) {
                $this->failed($error);
                exit(1);
            }

            $this->success(__('hf-shield.selected', ['value' => $selectedItem]));
            return $selectedItem;
        }

        exit(1); // Exit if no valid input
    }

    protected function selectTenant(): ?array
    {
        return $this->selectItem(
            __('hf-shield.tenant_list_prompt'),
            __('hf-shield.wrong_tenant_number'),
            fn () => $this->repository->retrieveTenantList()['data'] ?? []
        );
    }

    protected function retryIf(string $condition, string $label, string $field, array $conditions = [], bool $allowEmpty = false)
    {
        while (true) {
            $value = $this->ask(sprintf('%s:%s</>', $label, $allowEmpty ? '' : ' <fg=yellow>(*)'));
            if (! $value && $allowEmpty) {
                return $value;
            }

            $fieldExists = ! empty($this->repository->first([$field => $value, ...$conditions]));

            if ($this->validateCondition($fieldExists, $condition, $label, $value)) {
                return $value;
            }
        }
    }

    protected function validateCondition(bool $fieldExists, string $condition, string $label, string $value): bool
    {
        if ($condition === 'exists' && ! $fieldExists) {
            return true;
        }

        if ($condition === 'exists' && $fieldExists) {
            $this->warning(__('hf-shield.field_already_used', ['label' => $label, 'value' => $value]));
        }

        if ($condition === 'missing' && $fieldExists) {
            return true;
        }

        if ($condition === 'missing' && ! $fieldExists) {
            $this->warning(__('hf-shield.field_not_found', ['label' => $label, 'value' => $value]));
        }

        return false;
    }
}
