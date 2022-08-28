<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Model;

use Magento\Framework\App\CacheInterface;
use Magento\PageCache\Model\Cache\Type;

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
     * @var CacheInterface $cacheInterface,
     */
    protected CacheInterface $cacheInterface;

    /**
     * @var Type $cacheType
     */
    protected Type $cacheType;

    /**
     * @param CacheInterface $cacheInterface
     * @param Type $cacheType
     */
    public function __construct(
        CacheInterface $cacheInterface,
        Type $cacheType
    ) {
        $this->cacheInterface = $cacheInterface;
        $this->cacheType = $cacheType;
    }

    /**
     * Clean all feeds cache
     *
     * @return void
     */
    public function cleanAll(): void
    {
        $this->cacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::CODE]);
        $this->cacheInterface->remove(self::CODE . '_' . 'GOOGLE');
        $this->cacheInterface->remove(self::CODE . '_' . 'CENEO');
        $this->cacheInterface->remove(self::CODE . '_' . 'FACEBOOK');
        $this->cacheInterface->remove(self::CODE . '_' . 'REVIEWS');
    }
}
