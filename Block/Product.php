<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Block;

class Product extends \Magento\Catalog\Block\Product\AbstractProduct
{

    /**
     * @var \LCB\Feeds\Model\Product
     */
    protected $productModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $categoryCollectionFactory;

    /**
     * @var array
     * @since 1.1.0
     */
    public $categoryData;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \LCB\Feeds\Model\Product $productModel,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
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
        $collection->addStoreFilter($this->_storeManager->getStore()->getId());

        return $collection->getItems();
    }

    /**
     * @since 1.1.0
     * @return Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategories()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'google_category', 'ceneo_category']);
        return $collection;
    }

    /**
     * Convert product model into product feed model
     *
     * @return \LCB\Feeds\Model\Product
     */
    public function setProduct(\Magento\Catalog\Model\Product $product)
    {
        $product = $this->productModel->setData($product->getData());
        $product = $this->addCategoryData($product);
        return $product;
    }

    /**
     * Add calculated category data to products for speedup
     *
     * @since 1.1.0
     * @param \LCB\Feeds\Model\Product
     * @return \LCB\Feeds\Model\Product
     */
    public function addCategoryData($product)
    {
        if ($this->categoryData === null) {
            $this->categoryData = [];
            $collection = $this->getCategories();
            foreach ($collection as $category) {
                $this->categoryData[$category->getId()] = $category;
            }
        }

        $googleProductTypes = [];
        $googleCategory = '';
        $ceneoCategory = '';
        $categoryIds = array_reverse($product->getCategoryIds());

        foreach ($categoryIds as $categoryId) {
            if (isset($this->categoryData[$categoryId])) {
                $category = $this->categoryData[$categoryId];
                $categoryNames = [];
                $categoryPathIds = array_reverse(explode(',', $category->getPathInStore()));
                foreach ($categoryPathIds as $parentCategoryId) {
                    if (isset($this->categoryData[$parentCategoryId])) {
                        $categoryNames[] = $this->categoryData[$parentCategoryId]->getName();
                    }
                }
                $googleProductTypes[] = implode($categoryNames, ' > ');

                if (!$googleCategory) {
                    $googleCategory = $category->getGoogleCategory();
                }
                if (!$ceneoCategory) {
                    $ceneoCategory = $category->getCeneoCategory();
                }
            }
        }

        usort($googleProductTypes, function ($a, $b) {
            return strlen($b) - strlen($a);
        });
        $googleProductType = reset($googleProductTypes);

        $product->setData('google_product_type', $googleProductType);
        $product->setData('google_category', $googleCategory);
        $product->setData('ceneo_category', $ceneoCategory);

        return $product;
    }
}
