<?php

declare(strict_types=1);

namespace DFAU\VarnishCacheBackend\Encoding;

use TYPO3\CMS\Backend\Utility\BackendUtility;

trait CacheTagParser
{
    protected function parseTag(string $tag): array
    {
        $tag = \str_replace('pageId', 'pages', $tag);
        if (1 === \preg_match('/^([a-z0-9_]+)_(\d+)$/i', $tag, $matches)) {
            list($table, $uid) = BackendUtility::splitTable_Uid($tag);
            return ['string' => $table, 'int' => (int) $uid];
        }

        return ['string' => $tag];
    }
}
