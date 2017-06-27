<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Model;

class Review extends \Magento\Review\Model\Review {
 
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;
    
     /**
     * Extend class with additional methods
     */
    protected function _construct()
    {
        parent::_construct();
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();        
        $this->dateTime = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
    }
    
    /**
     * Get created at date in ISO 8601 format with Zulu zone designator
     * 
     * @return string
     */
    public function getTimestamp()
    {
        return $this->dateTime->date('Y-m-d\TH:i:s\Z', $this->getCreatedAt());
    }
    
}
