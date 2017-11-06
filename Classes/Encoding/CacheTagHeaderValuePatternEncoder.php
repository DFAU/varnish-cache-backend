<?php

namespace DFAU\VarnishCacheBackend\Encoding;


use DFAU\VarnishCacheBackend\Utility\Shortener;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class CacheTagHeaderValuePatternEncoder
{
    use CacheTagParser;

    const OPT_SHORTEN = 1;

    public function encode(string $tag, int $options = self::OPT_SHORTEN): string
    {
        $parsedTag = $this->parseTag($tag);
        return $this->buildHeaderValuePattern($parsedTag, $options & self::OPT_SHORTEN);
    }

    protected function buildHeaderValuePattern(array $tag, bool $shorten): string
    {
        $pattern = '.*;';
        $pattern .= $shorten ? Shortener::shortenString($tag['string']) : $tag['string'];
        $pattern .= '(?:_[^_]+)*';
        $pattern .= isset($tag['int']) ? ('(_' . ($shorten ? Shortener::shortenInteger($tag['int']) : $tag['int']) . ')') : '';
        $pattern .= '(?:_[^_]+)*';
        $pattern .= ';.*';
        return $pattern;
    }

}