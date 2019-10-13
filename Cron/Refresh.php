<?php

namespace LCB\Feeds\Cron;

use \Psr\Log\LoggerInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\PageCache\Model\Cache;

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */
class Refresh
{

    /**
     * @var int
     */
    const LAST_UPDATED_TIME = 84622200;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Collection
     */
    protected $productCollection;

    /**
     * @var Cache\Type
     */
    protected $cacheType;

    /**
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param Collection $productCollection
     * @parma Cache\Type $cacheType
     */
    public function __construct(
            LoggerInterface $logger,
            ScopeConfigInterface $scopeConfig,
            Collection $productCollection,
            Cache\Type $cacheType
        ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->productCollection = $productCollection;
        $this->cacheType = $cacheType;
    }

    /**
     * Clear cache if there are products updated last 24h
     */
    public function execute()
    {
        if ($this->productCollection
                ->addFieldToFilter('updated_at', ['from' => date('Y-m-d H:i:s', time() - SELF::LAST_UPDATED_TIME)])
                ->getSize()) {
            $this->cacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, ['FEEDS']);
        }
    }

}
