<?php

if (!\defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] = \DFAU\VarnishCacheBackend\Hooks\TyposcriptFrontendControllerHook::class . '->addContentPidToPageCacheTags';
