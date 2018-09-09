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
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    public $escaper;
    
    /**
     * Filter manager
     * 
     * @var \Magento\Framework\Filter\FilterManager
     */
    public $filterManager;

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
     * Extend class with additional methods
     */
    protected function _construct()
    {
        parent::_construct();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();        
        $this->escaper = $objectManager->create('Magento\Framework\Escaper');
        $this->filterManager = $objectManager->create('Magento\Framework\Filter\FilterManager');
        $this->stockItemRepository = $objectManager->create('Magento\CatalogInventory\Model\Stock\StockItemRepository');
        $this->priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        $this->imageHelper = $objectManager->create('Magento\Catalog\Helper\Image');
        $this->categoryModel = $objectManager->create('Magento\Catalog\Model\Category');
        $this->galleryReadHandler = $objectManager->create('Magento\Catalog\Model\Product\Gallery\ReadHandler');
    }

    /**
     * Get escaped product name
     *
     * @return string
     */
    public function getName()
    {
        return $this->escaper->escapeHtml(preg_replace('/[\x00-\x1F\x7F]/', '', parent::getName()));
    }

    /**
     * Get escaped product description limited to 5000 chars
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->filterManager->truncate($this->escaper->escapeHtml(parent::getDescription()), ['length' => 5000]);
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
    
        if(!$this->stock) {
            $this->stock = $this->stockItemRepository->get($this->getId());
        }
        
        return $this->stock;
        
    }
    
    /**
     * Get product quantity
     * 
     * @return int
     */
    public function getQty()
    {
        
        $qty = 0;

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

        try {
            $stockData = $this->stockItemRepository->get($this->getId());
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
        return $this->priceHelper->currency(parent::getPrice(), true, false);
    }
    
    /**
     * Get product price as string with currency
     * 
     * @return string
     */
    public function getSpecialPriceWithCurrency()
    {
        return $this->priceHelper->currency(parent::getSpecialPrice(), true, false);
    }
    
    /**
     * Get currency code only
     * 
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
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

        $ceneoCategory = '';

        if (!$this->getCategoryIds()) {
            return '';
        }
        $categoryIds = array_reverse($this->getCategoryIds());
        foreach ($categoryIds as $categoryId) {
            $ceneoCategory = $this->categoryModel->load($categoryId)->getCeneoCategory();
            if ($ceneoCategory) {
                break;
            }
        }

        return $ceneoCategory;
    }

}
