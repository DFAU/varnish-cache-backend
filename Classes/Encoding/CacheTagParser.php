<?php


namespace DFAU\VarnishCacheBackend\Encoding;


use TYPO3\CMS\Backend\Utility\BackendUtility;

trait CacheTagParser
{

    protected function parseTag(string $tag): array
    {
        $tag = str_replace('pageId', 'pages', $tag);
        if (preg_match('/^([a-z0-9_]+)_(\d+)$/i', $tag, $matches) === 1) {
            list($table, $uid) = BackendUtility::splitTable_Uid($tag);
            return ['string' => $table, 'int' => (int)$uid];
        } else {
            return ['string' => $tag];
        }
    }

}