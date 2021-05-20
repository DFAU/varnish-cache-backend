<?php

declare(strict_types=1);

namespace DFAU\VarnishCacheBackend\Utility;

class Shortener
{
    public static function shortenString(string $string): string
    {
        return \gmp_strval(\gmp_init(\hash('crc32b', $string), 16), 62);
    }

    public static function shortenInteger(int $integer): string
    {
        return \gmp_strval(\gmp_init($integer, 16), 62);
    }
}
