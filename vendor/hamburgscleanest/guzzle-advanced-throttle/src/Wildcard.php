<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle;

use Illuminate\Support\Arr;

/**
 * Class Wildcard
 * @package hamburgscleanest\GuzzleAdvancedThrottle
 */
class Wildcard
{

    private const REGEX_WILDCARD = '/{[^}]*}/';
    private const REGEX_ANY_CHAR = '(.*)';
    private const REGEX_DELIMITER = '/';

    /**
     * @param  string  $wildcardText
     * @param  string  $text
     * @return bool
     */
    public static function matches(string $wildcardText, string $text): bool
    {
        $regex = \preg_replace(self::REGEX_WILDCARD, self::REGEX_ANY_CHAR, $wildcardText);

        $matches = [];
        \preg_match_all(
            self::REGEX_DELIMITER.\str_replace(self::REGEX_DELIMITER, '\\'.self::REGEX_DELIMITER,
                $regex).self::REGEX_DELIMITER,
            $text,
            $matches
        );

        \array_shift($matches);

        return \strtr($text, \array_fill_keys(Arr::flatten($matches), self::REGEX_ANY_CHAR)) === $regex;
    }
}
