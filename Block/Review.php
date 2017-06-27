<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Block;

class Review extends \Magento\Review\Block\Product\Review {

    /**
     * @var \LCB\Feeds\Model\Review $reviewModel
     */
    protected $_reviewModel;
    
    /**
     * Review resource model
     *
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $_reviewsColFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \LCB\Feeds\Model\Review $reviewModel,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_reviewsColFactory = $collectionFactory;
        $this->_reviewModel = $reviewModel;
        parent::__construct($context, $registry, $collectionFactory, $data);
    }
    
    /**
     * @return \Magento\Framework\DataObject[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getReviews()
    {
       $collection = $this->_reviewsColFactory->create()->addStoreFilter(
            $this->_storeManager->getStore()->getId()
        )->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED
        )->addRateVotes();

        return $collection->getItems();
    }

    /**
     * Convert review model into review feed model
     * 
     * @return \LCB\Feeds\Model\Review
     */
    public function setReview(\Magento\Review\Model\Review $review)
    {
        return $this->_reviewModel->setData($review->getData());
    }

}
