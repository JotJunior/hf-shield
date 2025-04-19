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

use Hyperf\Cache\Helper\StringHelper;
use Hyperf\Stringable\Str;
use function Hyperf\Collection\data_get;

class CacheStringHelperWrapper extends StringHelper
{
    /**
     * Format cache key with prefix and arguments.
     */
    public static function format(string $prefix, array $arguments, ?string $value = null): string
    {
        if ($value !== null) {
            if ($matches = StringHelper::parse($value)) {
                foreach ($matches as $search) {
                    $k = str_replace(['#{', '}'], '', $search);

                    $value = Str::replaceFirst($search, (string)  data_get($arguments, $k), $value);
                }
            }
        } else {
            foreach ($arguments as &$argument) {
                if (is_array($argument)) {
                    $argument = md5(json_encode($argument));
                }
            }
            $value = implode(':', $arguments);
        }

        return $prefix . ':' . $value;
    }
}
