<?php


namespace DFAU\VarnishCacheBackend\Cache\Backend;


use DFAU\VarnishCacheBackend\Encoding\CacheTagHeaderValuePatternEncoder;
use DFAU\VarnishCacheBackend\Encoding\CacheTagsHeaderValueEncoder;
use DFAU\VarnishCacheBackend\Varnish\BanList;
use TYPO3\CMS\Core\Cache\Backend\AbstractBackend;
use TYPO3\CMS\Core\Cache\Backend\TaggableBackendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class VarnishBackend extends AbstractBackend implements TaggableBackendInterface
{
    const HEADER_CACHE_HASH = 'TYPO3-Cache-Hash';
    const HEADER_CACHE_TAGS = 'TYPO3-Cache-Tags';
    const HEADER_VALUE_SEPARATOR = ': ';

    /**
     * @var bool
     */
    protected $compression = false;

    /**
     * @var BanList
     */
    protected $banList;

    /**
     * @var CacheTagsHeaderValueEncoder
     */
    protected $cacheTagsHeaderValueEncoder;

    /**
     * @var CacheTagHeaderValuePatternEncoder
     */
    protected $cacheTagHeaderPatternEncoder;

    /**
     * VarnishBackend constructor.
     * @param string $context
     * @param array $options
     */
    public function __construct($context, array $options = [])
    {
        parent::__construct($context, $options);

        $this->banList = GeneralUtility::makeInstance(BanList::class);
        $this->cacheTagsHeaderValueEncoder = GeneralUtility::makeInstance(CacheTagsHeaderValueEncoder::class);
        $this->cacheTagHeaderPatternEncoder = GeneralUtility::makeInstance(CacheTagHeaderValuePatternEncoder::class);
    }

    /**
     * @param string $banRequestMethod
     */
    public function setBanRequestMethod(string $banRequestMethod)
    {
        $this->banList->setBanRequestMethod($banRequestMethod);
    }

    /**
     * @param array $instanceHostnames
     */
    protected function setInstanceHostnames(array $instanceHostnames)
    {
        $this->banList->setInstanceHostnames($instanceHostnames);
    }

    /**
     * @param bool $compression
     */
    public function setCompression(bool $compression)
    {
        $this->compression = $compression;
    }

    /**
     * Saves data in the cache.
     *
     * @param string $entryIdentifier An identifier for this specific cache entry
     * @param string $data The data to be stored
     * @param array $tags Tags to associate with this cache entry. If the backend does not support tags, this option can be ignored.
     * @param int $lifetime Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited lifetime.
     * @return void
     * @throws \TYPO3\CMS\Core\Cache\Exception if no cache frontend has been set.
     * @throws \TYPO3\CMS\Core\Cache\Exception\InvalidDataException if the data is not a string
     * @api
     */
    public function set($entryIdentifier, $data, array $tags = [], $lifetime = null)
    {
        $GLOBALS['TSFE']->config['config']['additionalHeaders.']['1506001460.']['header'] = static::HEADER_CACHE_HASH . static::HEADER_VALUE_SEPARATOR . $entryIdentifier;

        $headerValue = $this->cacheTagsHeaderValueEncoder->encode($tags, $this->compression ? $this->cacheTagsHeaderValueEncoder::OPT_SHORTEN : 0);
        $GLOBALS['TSFE']->config['config']['additionalHeaders.']['1504798687.']['header'] = static::HEADER_CACHE_TAGS . static::HEADER_VALUE_SEPARATOR . $headerValue;
    }

    /**
     * Loads data from the cache.
     *
     * @param string $entryIdentifier An identifier which describes the cache entry to load
     * @return mixed The cache entry's content as a string or FALSE if the cache entry could not be loaded
     * @api
     */
    public function get($entryIdentifier)
    {
        return "";
    }

    /**
     * Checks if a cache entry with the specified identifier exists.
     *
     * @param string $entryIdentifier An identifier specifying the cache entry
     * @return bool TRUE if such an entry exists, FALSE if not
     * @api
     */
    public function has($entryIdentifier)
    {
        return false;
    }

    /**
     * Removes all cache entries matching the specified identifier.
     * Usually this only affects one entry but if - for what reason ever -
     * old entries for the identifier still exist, they are removed as well.
     *
     * @param string $entryIdentifier Specifies the cache entry to remove
     * @return bool TRUE if (at least) an entry could be removed or FALSE if no entry was found
     * @api
     */
    public function remove($entryIdentifier)
    {
        $this->banList->addBan(static::HEADER_CACHE_HASH . static::HEADER_VALUE_SEPARATOR . $entryIdentifier);
    }

    /**
     * Removes all cache entries of this cache.
     *
     * @return void
     * @api
     */
    public function flush()
    {
        $this->banList->addBan(static::HEADER_CACHE_HASH . static::HEADER_VALUE_SEPARATOR . '.*');
    }

    /**
     * Does garbage collection
     *
     * @return void
     * @api
     */
    public function collectGarbage()
    {
    }

    /**
     * Removes all cache entries of this cache which are tagged by the specified tag.
     *
     * @param string $tag The tag the entries must have
     * @return void
     * @api
     */
    public function flushByTag($tag)
    {
        $headerValue = $this->cacheTagHeaderPatternEncoder->encode(
            $tag,
            $this->compression ? $this->cacheTagHeaderPatternEncoder::OPT_SHORTEN : 0
        );
        $this->banList->addBan(static::HEADER_CACHE_TAGS . static::HEADER_VALUE_SEPARATOR . $headerValue);
    }

    public function flushByCustomBan(string $banHeader)
    {
        $this->banList->addBan($banHeader);
    }

    /**
     * Finds and returns all cache entry identifiers which are tagged by the
     * specified tag
     *
     * @param string $tag The tag to search for
     * @return array An array with identifiers of all matching entries. An empty array if no entries matched
     * @api
     */
    public function findIdentifiersByTag($tag)
    {
        return [];
    }
}
