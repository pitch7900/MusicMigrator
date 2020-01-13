<?php

namespace hamburgscleanest\GuzzleAdvancedThrottle\Tests;

use hamburgscleanest\GuzzleAdvancedThrottle\Wildcard;
use PHPUnit\Framework\TestCase;

/**
 * Class WildcardTest
 * @package hamburgscleanest\GuzzleAdvancedThrottle\Tests
 */
class WildcardTest extends TestCase
{

    /** @test
     */
    public function matches_wildcards() : void
    {
        static::assertTrue(Wildcard::matches('test1.{wildcard1}.test2.{wildcard2}.test3', 'test1.replace1.test2.replace2.test3'));
    }

    /** @test
     */
    public function does_not_match_when_not_all_wildcards_given() : void
    {
        $wildcardText = 'test1.{wildcard1}.test2.{wildcard2}.test3';

        static::assertFalse(Wildcard::matches($wildcardText, 'no_wildcards'));
        static::assertFalse(Wildcard::matches($wildcardText, 'test1.{wildcard1}.test2.test3'));
        static::assertFalse(Wildcard::matches($wildcardText, 'test1.{wildcard1}.test2.{wildcard2}'));
        static::assertFalse(Wildcard::matches($wildcardText, 'one.{wildcard1}.two.{wildcard2}.three'));
    }

    /** @test
     */
    public function ignores_text_without_wildcard() : void
    {
        $wildcardText = 'test1';

        static::assertTrue(Wildcard::matches($wildcardText, $wildcardText));
        static::assertFalse(Wildcard::matches($wildcardText, 'test2'));
    }
}