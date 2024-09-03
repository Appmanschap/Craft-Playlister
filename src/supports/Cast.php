<?php

namespace appmanschap\youtubeplaylistimporter\supports;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 */
class Cast
{
    /**
     * @param mixed $value
     * @return int
     */
    public static function mixedToInt(mixed $value): int
    {
        return match (true) {
            is_bool($value),
            is_string($value),
            is_int($value),
            is_float($value),
            is_resource($value) => intval($value),
            default => 0
        };
    }

    /**
     * @param mixed $value
     * @return string
     */
    public static function mixedToString(mixed $value): string
    {
        return match (true) {
            is_bool($value),
            is_string($value),
            is_int($value),
            is_float($value),
            is_resource($value) => strval($value),
            default => ''
        };
    }
}
