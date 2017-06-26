<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Block;

class Feed extends \Magento\Catalog\Block\Product\AbstractProduct {

    /**
     * @var \LCB\Feeds\Model\Product $productModel
     */
    protected $productModel;
    
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
    \Magento\Catalog\Block\Product\Context $context, 
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, 
    \LCB\Feeds\Model\Product $productModel,        
    \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus, 
    \Magento\Catalog\Model\Product\Visibility $productVisibility, 
    array $data = []
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productModel = $productModel;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\DataObject[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProducts()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
                ->addAttributeToFilter('visibility', ['in' => $this->productVisibility->getVisibleInSiteIds()]);

        return $collection->getItems();
    }
    
    /**
     * Convert product model into product feed model
     * 
     * @return \LCB\Feeds\Model\Product
     */
    public function setProduct(\Magento\Catalog\Model\Product $product)
    {
        return $this->productModel->setData($product->getData());
    }

}
