<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Model;

class Cache
{

    /**
     * @var int
     */
    const LIFETIME = 86400;

    /**
     * @var string
     */
    const CODE = 'FEEDS';

    /**
     * @var \Magento\Framework\App\CacheInterface $cacheInterface,
     */
    protected $cacheInterface;

    /**
     * @var \Magento\PageCache\Model\Cache\Type $cacheType
     */
    protected $cacheType;

    /**
     * @param \Magento\Framework\App\CacheInterface $cacheInterface
     * @param \Magento\PageCache\Model\Cache\Type $cacheType
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cacheInterface,
        \Magento\PageCache\Model\Cache\Type $cacheType
    ) {
        $this->cacheInterface = $cacheInterface;
        $this->cacheType = $cacheType;
    }

    /**
     * Clean all feeds cache
     */
    public function cleanAll()
    {
        $this->cacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::CODE]);
        $this->cacheInterface->remove(self::CODE . '_' . 'GOOGLE');
        $this->cacheInterface->remove(self::CODE . '_' . 'CENEO');
        $this->cacheInterface->remove(self::CODE . '_' . 'FACEBOOK');
        $this->cacheInterface->remove(self::CODE . '_' . 'REVIEWS');
    }
}
