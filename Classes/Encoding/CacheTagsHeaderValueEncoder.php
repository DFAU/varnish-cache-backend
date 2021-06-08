<?php

declare(strict_types=1);

namespace DFAU\VarnishCacheBackend\Encoding;

use DFAU\VarnishCacheBackend\Utility\Shortener;

class CacheTagsHeaderValueEncoder
{
    use CacheTagParser;

    const OPT_SHORTEN = 1;

    public function encode(array $tags, int $options = self::OPT_SHORTEN): string
    {
        $parsedTags = \array_map([$this, 'parseTag'], \array_unique($tags));
        $groupedTags = $this->groupTags($parsedTags);
        return $this->buildHeaderValue($groupedTags, (bool) ($options & self::OPT_SHORTEN));
    }

    protected function groupTags(array $parsedTags): array
    {
        $groupedTags = [];
        foreach ($parsedTags as $parsedTag) {
            if (!isset($groupedTags[$parsedTag['string']])) {
                $groupedTags[$parsedTag['string']] = [];
            }

            if (isset($parsedTag['int'])) {
                $groupedTags[$parsedTag['string']][] = $parsedTag['int'];
            }
        }

        return $groupedTags;
    }

    protected function buildHeaderValue(array $groupedTags, bool $shorten): string
    {
        $groupedTags = \array_map(
            function ($tags, $group) use ($shorten): string {
                $tags = \array_map(function ($tag) use ($shorten): string {
                    if (!$tag) {
                        return '';
                    }

                    return $shorten ? Shortener::shortenInteger($tag) : $tag;
                }, $tags);
                return ($shorten ? Shortener::shortenString($group) : $group) . (0 !== \count($tags) ? '_' . \implode('_', $tags) : '');
            },
            $groupedTags,
            \array_keys($groupedTags)
        );
        return ';' . \implode(';', $groupedTags) . ';';
    }
}
