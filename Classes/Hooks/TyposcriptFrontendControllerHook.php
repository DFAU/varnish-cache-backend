<?php


namespace DFAU\VarnishCacheBackend\Hooks;


use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class TyposcriptFrontendControllerHook
{

    public function addContentPidToPageCacheTags(array $params, TypoScriptFrontendController $parent)
    {
        if ($parent->contentPid) {
            $parent->addCacheTags(['pages_' . $parent->contentPid]);
        }
    }

}