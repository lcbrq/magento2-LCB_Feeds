<?php

namespace LCB\Feeds\Controller\Adminhtml\Cache;

use LCB\Feeds\Model\Cache;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */
class GenerateFeeds extends Action
{

    /**
     * @var \LCB\Feeds\Model\Cache
     */
    protected $cache;

    /**
     * @param Action\Context $context
     * @param \LCB\Feeds\Model\Cache $cache
     */
    public function __construct(
        Action\Context $context,
        Cache $cache
    ) {
        parent::__construct($context);
        $this->cache = $cache;
    }

    /**
     * Refresh feeds cache from admin button trigger
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->cache->cleanAll();
        $this->messageManager->addSuccess(__('Product feeds cache has been cleaned.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*');
    }
}
