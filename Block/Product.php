<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Block;

use LCB\Feeds\Model\Product as ProductModel;
use LCB\Feeds\Model\ProductFactory as ProductModelFactory;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Product extends AbstractProduct
{

    /**
     * @var ProductModelFactory
     */
    protected ProductModelFactory $productModelFactory;

    /**
     * @var ProductCollectionFactory
     */
    public ProductCollectionFactory $productCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    public CategoryCollectionFactory $categoryCollectionFactory;

    /**
     * @var array
     * @since 1.1.0
     */
    public $categoryData;

    /**
     * @param Contect $context
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param ProductModelFactory $productFactory
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @throws LocalizedException
     */
    public function __construct(
        Context $context,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        ProductModelFactory $productModelFactory,
        Status $productStatus,
        Visibility $productVisibility,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productModelFactory = $productModelFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        parent::__construct($context, $data);
    }

    /**
     * @return DataObject[]
     * @throws LocalizedException
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
     * @return CategoryCollection
     */
    public function getCategories(): CategoryCollection
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'google_category', 'ceneo_category']);
        return $collection;
    }

    /**
     * Convert product model into product feed model
     *
     * @return ProductModel
     */
    public function setProduct(\Magento\Catalog\Model\Product $product): ProductModel
    {
        $product = $this->productModelFactory->create()->setData($product->getData());
        $product = $this->addCategoryData($product);
        return $product;
    }

    /**
     * Add calculated category data to products for speedup
     *
     * @since 1.1.0
     * @param ProductModel
     * @return ProductModel
     */
    public function addCategoryData(ProductModel $product): ProductModel
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
                $googleProductTypes[] = implode(' > ', $categoryNames);

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
