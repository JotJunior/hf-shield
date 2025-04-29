<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Helper;

use League\OAuth2\Server\CryptTrait;
use Monolog\Formatter\ElasticsearchFormatter;

class ShieldElasticsearchFormatter extends ElasticsearchFormatter
{
    use CryptTrait;

    public function __construct(string $index, string $type, string $encryptionKey)
    {
        parent::__construct($index, $type);
        $this->setEncryptionKey($encryptionKey);
    }

    protected function getDocument(array $record): array
    {
        $record['@timestamp'] = $record['datetime'];
        $record['server_params'] = $record['context']['server_params'] ?? null;
        $record['user'] = $record['context']['user'] ?? null;
        $record['access'] = $record['context']['access'] ?? null;
        $record['deleted'] = false;
        $record['@version'] = 1;
        unset(
            $record['context']['server_params'],
            $record['context']['user'],
            $record['context']['message'],
            $record['context']['access']
        );

        foreach ($record['context']['headers'] as $key => &$header) {
            if (str_starts_with(strtolower($header[0]), 'bearer')) {
                $header[0] = 'Bearer ******';
            }
        }

        $shouldEncrypt = false;
        $original = $record;
        $record = $this->anonymizeData($record, $shouldEncrypt);
        if ($shouldEncrypt) {
            $record['encrypted_context'] = $this->encrypt(json_encode($original));
        }

        return parent::getDocument($record);
    }

    private function anonymizeData(array $data, bool &$shouldEncrypt = false): array|string
    {
        foreach ($data as $key => $value) {
            if ($this->isBase64Image($value)) {
                $data[$key] = $this->truncateString((string) $value);
            }

            if (is_array($value)) {
                $anonymizedValue = $this->anonymizeData($value, $shouldEncrypt);
                if ($this->shouldSerializeField($key)) {
                    $data[$key] = json_encode($anonymizedValue);
                } else {
                    $data[$key] = $anonymizedValue;
                }
                continue;
            }

            if ($this->isPasswordField($key)) {
                $shouldEncrypt = true;
                $data[$key] = $this->maskPassword();
                continue;
            }

            if ($this->isFederalDocument($key)) {
                $shouldEncrypt = true;
                $data[$key] = $this->maskFederalDocument($value);
                continue;
            }

            if ($this->isPhoneField($key)) {
                $shouldEncrypt = true;
                $data[$key] = $this->maskPhone((string) $value);
                continue;
            }

            if ($this->isEmailField($key)) {
                $shouldEncrypt = true;
                $data[$key] = $this->maskEmail((string) $value);
                continue;
            }
        }
        return $data;
    }

    private function isBase64Image(mixed $value): bool
    {
        return ! empty($value) && is_string($value) && str_contains('data:image', $value);
    }

    private function truncateString(mixed $value): string
    {
        if (is_string($value) && strlen($value) > 100) {
            return substr($value, 0, 20) . '...';
        }
        return $value;
    }

    private function shouldSerializeField(mixed $key): bool
    {
        if (! is_string($key)) {
            return false;
        }
        return in_array($key, ['addresse'], true);
    }

    private function isPasswordField(mixed $key): bool
    {
        return is_string($key) && stripos($key, 'password') !== false;
    }

    private function maskPassword(): string
    {
        return '*********';
    }

    private function isFederalDocument(mixed $key): bool
    {
        return is_string($key) && stripos($key, 'federal_document') !== false;
    }

    private function maskFederalDocument(mixed $value): mixed
    {
        $value = preg_replace('/\D/', '', (string) $value);
        $pattern = '/^(\d{3})(\d{3})(\d{3})(\d{2})$/';
        if (strlen($value) === 11 && preg_match($pattern, (string) $value, $matches)) {
            return sprintf('%s.***.***-%s', $matches[1], $matches[4]);
        }
        $pattern = '/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/';
        if (strlen($value) === 14 && preg_match($pattern, (string) $value, $matches)) {
            return sprintf('**.%s.***/%s-%s', $matches[2], $matches[3], $matches[5]);
        }
        return $value;
    }

    private function isPhoneField(mixed $key): bool
    {
        return is_string($key) && (stripos($key, 'phone') !== false || stripos($key, 'mobile') !== false);
    }

    private function maskPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        $chars = mb_str_split($phone);
        $digitIndexes = [];
        foreach ($chars as $index => $char) {
            if (ctype_digit($char)) {
                $digitIndexes[] = $index;
            }
        }
        $totalDigits = count($digitIndexes);
        foreach ($digitIndexes as $seq => $charIndex) {
            $posFromRight = $totalDigits - $seq;
            if ($posFromRight >= 1 && $posFromRight <= 2) {
                continue;
            }
            if ($posFromRight >= 3 && $posFromRight <= 4) {
                $chars[$charIndex] = '*';
                continue;
            }
            if ($posFromRight >= 5 && $posFromRight <= 6) {
                continue;
            }
            if ($posFromRight >= 7 && $posFromRight <= 8) {
                $chars[$charIndex] = '*';
            }
        }
        return implode('', $chars);
    }

    private function isEmailField(mixed $key): bool
    {
        return is_string($key) && stripos($key, 'email') !== false;
    }

    private function maskEmail(string $email): string
    {
        if (strpos($email, '@') === false) {
            return $email;
        }
        [$local, $domain] = explode('@', $email, 2);
        if (strlen($local) < 2) {
            return $email;
        }
        // Preserva a primeira e a última letra e utiliza 5 asteriscos fixos no meio.
        return substr($local, 0, 1) . '*****' . substr($local, -1) . '@' . $domain;
    }
}
