<?php

namespace LCB\Feeds\Controller\Adminhtml\Cache;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use LCB\Cache\Model\Cache;

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */
class GenerateFeeds extends \Magento\Backend\App\Action
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
        \LCB\Feeds\Model\Cache $cache
    )
    {
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
