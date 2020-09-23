<?php
namespace LCB\Feeds\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\View\Result\PageFactory;

abstract class Feed extends \Magento\Framework\App\Action\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CacheInterface $cache
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CacheInterface $cache
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->cache = $cache;
    }

    /**
     * Get feed data from cache
     *
     * @param string $feedCacheType
     * @return string|null
     */
    public function getFromCache($feedCacheType)
    {
        return $this->cache->load(\LCB\Feeds\Model\Cache::CODE . '_' . strtoupper($feedCacheType));
    }
}
