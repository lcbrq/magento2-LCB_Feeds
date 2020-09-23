<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Model;

class Product extends \Magento\Catalog\Model\Product {


    /**
     * Stock item data container
     * 
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    public $stockItemRepository;
    
    /**
     * Price helper for price render
     * 
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    public $priceHelper;
    
    /**
     * @var Magento\Catalog\Helper\Image
     */
    public $imageHelper;
    
    /**
     * Category model for feed
     * 
     * @var \Magento\Catalog\Model\Category
     */
    public $categoryModel;
    
    /**
     * Gallery handler for all images load
     * 
     * @var \Magento\Catalog\Model\Product\Gallery\ReadHandler
     */
    public $galleryReadHandler;
    
    /**
     * Stock item
     * 
     * @var \Magento\CatalogInventory\Model\Stock
     */
    public $stock;

   /**
    * MSI Stock Id
    *
    * @param int
    * @since 2.3.0
    */
    protected $msiStockId;

   /**
    * MSI Stock Data Item Interface
    *
    * @since 2.3.0
    * @var \Magento\InventorySalesApi\Api\GetStockItemDataInterface
    */
    protected $msiStockDataInterface;

   /**
    * MSI Stock Data
    *
    * @since 2.3.0
    * @var array
    */
    protected $msiStockData = array();

    /**
     * Extend class with additional methods
     */
    protected function _construct()
    {
        parent::_construct();

        $this->stockItemRepository = $this->getData('stockItemRepository');
        $this->priceHelper = $this->getData('priceHelper');
        $this->imageHelper = $this->getData('imageHelper');
        $this->categoryModel = $this->getData('categoryModel');
        $this->galleryReadHandler = $this->getData('galleryReadHandler');

        try {
            if (class_exists('\Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite')) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $this->msiStockId = $objectManager->get('\Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite')->execute();
                $this->msiStockDataInterface = $objectManager->get('\Magento\InventorySalesApi\Model\GetStockItemDataInterface');
            }
        } catch(\Exception $e) {}

    }

    /**
     * @return string
     */
    public function getName()
    {
        return parent::getName();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return parent::getDescription();
    }

    /**
     * Get product condition
     * 
     * @return string
     */
    public function getCondition()
    {
        $condition = parent::getCondition();
        if (!$condition) {
            $condition = 'new';
        }
        return $condition;
    }
    
    /**
     * Get stock item
     * 
     * @throws \Exception
     */
    public function getStock() 
    {
    
        if (!$this->stock) {
            $this->stock = $this->stockItemRepository->get($this->getId());
        }
        
        return $this->stock;
        
    }

   /**
    * Get MSI stock data
    *
    */
    public function getMsiStock()
    {
        if (!$this->msiStockData && $this->msiStockId && $this->msiStockDataInterface) {
            $this->msiStockData = $this->msiStockDataInterface->execute($this->getSku(), $this->msiStockId);
        }

        return $this->msiStockData;
    }

    /**
     * Get product quantity
     * 
     * @return int
     */
    public function getQty()
    {
        
        $qty = 0;

        $msiData = $this->getMsiStock();
        if ($msiData && isset($msiData['quantity'])) {
            return $msiData['quantity'];
        }

        try {
          $qty = $this->getStock()->getQty();
        } catch (\Exception $e) {
            // skip
        }

        return $qty;
    }

    /**
     * Get stock availability
     * 
     * @return bool
     */
    public function getAvailability()
    {
        
        $availability = false;

        $msiData = $this->getMsiStock();
        if ($msiData && isset($msiData['is_salable'])) {
            return (bool) $msiData['is_salable'];
        }

        try {
            if ($this->getStock()->getIsInStock()) {
                $availability = true;
            }
        } catch (\Exception $e) {
            // skip
        }

        return $availability;
    }

    /**
     * Check if product has special price
     * 
     * @return bool
     */
    public function hasSpecialPrice()
    {
        return $this->getPrice() > $this->getFinalPrice();
    }
    
    /**
     * Get product price as string with currency
     * 
     * @return string
     */
    public function getPriceWithCurrency()
    {
        return str_replace("\xc2\xa0", ' ', $this->priceHelper->currency(parent::getPrice(), true, false));
    }

    /**
     * Get product price as string with currency
     * 
     * @return string
     */
    public function getSpecialPriceWithCurrency()
    {
        return str_replace("\xc2\xa0", ' ', $this->priceHelper->currency(parent::getSpecialPrice(), true, false));
    }

    /**
     * Get currency code only
     * 
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->_storeManager->getCurrentCurrency()->getCode();
    }

    /**
     * Get product image url
     * 
     * @return string
     */
    public function getImageUrl($type = 'product_page_main_image_default')
    {
        return $this->imageHelper->init($this, $type)->getUrl();
    }
    
    /**
     * Get product images urls
     * 
     * @return array
     */
    public function getImages()
    {
        $this->galleryReadHandler->execute($this);
        return $this->getMediaGalleryImages();
    }
    
    /**
     * Get Ceneo category
     * 
     * @return string
     */
    public function getCeneoCategory()
    {

        if ($ceneoCategory = (string) $this->getData('ceneo_category')) {
            return $ceneoCategory;
        }

        if (!$this->getCategoryIds()) {
            return '';
        }

        $categoryIds = array_reverse($this->getCategoryIds());
        foreach ($categoryIds as $categoryId) {
            $categoryModel = $this->categoryModel;
            if (!$categoryModel->getResource() instanceof \Magento\Catalog\Model\ResourceModel\Category\Flat) {
                $ceneoCategory = $categoryModel->getResource()->getAttributeRawValue($categoryId, 'ceneo_category', $this->getStoreId());
            } else {
                $ceneoCategory = $categoryModel->load($categoryId)->getCeneoCategory();
            }
            if ($ceneoCategory) {
                break;
            }
        }

        return $ceneoCategory;
    }

    /**
     * Get Google category
     *
     * @return string
     */
    public function getGoogleCategory()
    {

        if ($googleCategory = (string) $this->getData('google_category')) {
            return $googleCategory;
        }

        if (!$this->getCategoryIds()) {
            return '';
        }

        $categoryIds = array_reverse($this->getCategoryIds());
        foreach ($categoryIds as $categoryId) {
            $categoryModel = $this->categoryModel;
            if (!$categoryModel->getResource() instanceof \Magento\Catalog\Model\ResourceModel\Category\Flat) {
                $googleCategory = $categoryModel->getResource()->getAttributeRawValue($categoryId, 'google_category', $this->getStoreId());
            } else {
                $googleCategory = $categoryModel->load($categoryId)->getGoogleCategory();
            }
            if ($googleCategory) {
                break;
            }
        }

        return $googleCategory;
    }

    /**
     * Get Google product type from store categories
     *
     * @return string
     */
    public function getGoogleProductType() {

        if ($googleProductType = (string) $this->getData('google_product_type')) {
            return $googleProductType;
        }

        if (!$this->getCategoryIds()) {
            return '';
        }

        $categoryIds = array_reverse($this->getCategoryIds());
        $categoryNames = [];
        foreach ($categoryIds as $categoryId) {
            $category = $this->categoryModel->load($categoryId);
            $categories = $category->getParentCategories();
            foreach ($categories as $category) {
                $categoryNames[] = $category->getName();
            }
            break;
        }

        $googleProductType = implode($categoryNames, ' > ');
        return $googleProductType;

    }

    /**
     * Get product visibility in feed
     *
     * @return boolean
     */
    public function isVisibleInFeed()
    {
        return $this->isVisibleInCatalog();
    }

}
