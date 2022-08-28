<?php

namespace LCB\Feeds\Cron;

use LCB\Feeds\Console\Command\Generate;
use LCB\Feeds\Model\Cache;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

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
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Generate
     */
    protected $generator;

    /**
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Collection $productCollection
     * @param StoreManager $storeManager
     * @param Cache $cache
     * @param Generate $generate
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Collection $productCollection,
        Cache $cache,
        Generate $generate
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->productCollection = $productCollection;
        $this->cache = $cache;
        $this->generator = $generate;
    }

    /**
     * Clear cache if there are products updated last 24h
     */
    public function execute()
    {
        if ($this->productCollection
                ->addFieldToFilter('updated_at', ['from' => date('Y-m-d H:i:s', time() - SELF::LAST_UPDATED_TIME)])
                ->getSize()) {
            $this->cache->cleanAll();
            try {
                foreach ($this->storeManager->getStores() as $store) {
                    if ($store->getIsActive()) {
                        $this->generator->generate($store->getCode(), 'google');
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
