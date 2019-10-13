<?php

namespace LCB\Feeds\Controller\Adminhtml\Cache;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\PageCache\Model\Cache;

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
     * @param Action\Context $context
     * @param Cache\Type $cacheType
     */
    public function __construct(
        Action\Context $context,
        Cache\Type $cacheType
    )
    {
        parent::__construct($context);
        $this->cacheType = $cacheType;
    }

    /**
     * Refresh feeds cache from admin button trigger
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->cacheType->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, ['FEEDS']);
        $this->messageManager->addSuccess(__('Product feeds cache has been cleaned.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*');
    }
}
