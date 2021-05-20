<?php

declare(strict_types=1);

namespace DFAU\VarnishCacheBackend\Varnish;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BanList implements SingletonInterface
{
    /** @var string */
    protected $banRequestMethod = 'BAN';

    /** @var array */
    protected $instanceHostnames = [];

    /** @var \resource */
    protected $curlQueue;

    public function __construct(string $banRequestMethod = null, array $instanceHostnames = null)
    {
        if (null !== $banRequestMethod) {
            $this->banRequestMethod = $banRequestMethod;
        }

        if (null === $instanceHostnames) {
            $this->instanceHostnames[] = GeneralUtility::getIndpEnv('HTTP_HOST');
        }

        $this->curlQueue = \curl_multi_init();
    }

    public function setBanRequestMethod(string $banRequestMethod)
    {
        $this->banRequestMethod = $banRequestMethod;
    }

    public function setInstanceHostnames(array $instanceHostnames)
    {
        $this->instanceHostnames = $instanceHostnames;
    }

    public function addBan(string $banHeader)
    {
        foreach ($this->instanceHostnames as $instanceHostname) {
            $this->addCommand($this->banRequestMethod, $instanceHostname, [$banHeader]);
        }
    }

    protected function addCommand(string $method, string $url, array $header = [])
    {
        $curlHandle = \curl_init();
        $curlOptions = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_TIMEOUT => 1,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        \curl_setopt_array($curlHandle, $curlOptions);
        \curl_multi_add_handle($this->curlQueue, $curlHandle);
    }

    protected function runQueue()
    {
        $running = null;
        do {
            \curl_multi_exec($this->curlQueue, $running);
        } while ($running);

        // destroy Handle which is not required anymore
        \curl_multi_close($this->curlQueue);
    }

    public function __destruct()
    {
        // execute cURL Multi-Handle Queue
        $this->runQueue();
    }
}
