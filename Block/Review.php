<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Block;

use LCB\Feeds\Model\Review as FeedReviewModel;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;

class Review extends \Magento\Review\Block\Product\Review
{

    /**
     * @var Registry
     */
    protected Registry $coreRegistry;

    /**
     * @var FeedReviewModel
     */
    protected FeedReviewModel $reviewModel;

    /**
     * Review resource model
     *
     * @var ReviewCollectionFactory
     */
    protected $reviewsCollectionFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FeedReviewModel $reviewModel
     * @param ReviewCollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FeedReviewModel $reviewModel,
        ReviewCollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->reviewsCollectionFactory = $collectionFactory;
        $this->reviewModel = $reviewModel;
        parent::__construct($context, $registry, $collectionFactory, $data);
    }

    /**
     * @return \Magento\Framework\DataObject[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getReviews()
    {
        $collection = $this->reviewsCollectionFactory->create()->addStoreFilter(
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
        return $this->reviewModel->setData($review->getData());
    }
}
