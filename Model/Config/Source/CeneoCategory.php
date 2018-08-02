<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class CeneoCategory extends AbstractSource
{
    
    /**
     * @var string
     */
    const MODULE_NAME = 'LCB_Feeds';
    
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;
    
    /**
     * @var array
     */
    public $categories;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        $this->moduleReader = $moduleReader;
    }

    /**
     * Get Ceneo Categories
     * 
     * @return array
     */
    public function getAllOptions()
    {

        if ($this->categories) {
            return $this->categories;
        }

        $sourceDir = $this->moduleReader->getModuleDir(
                \Magento\Framework\Module\Dir::MODULE_ETC_DIR, self::MODULE_NAME
        );

        $ceneoXml = simplexml_load_file($sourceDir . '/source/ceneo.xml');
        $categories = ['label' => 'Please select', 'value' => ''];

        foreach ($ceneoXml as $category) {
            $categories[] = array('label' => (string) $category->Name, 'value' => (string) $category->Name);
            $categories = array_merge($categories, $this->appendSubcategories($category, 1, $category->Name));
        }

        $this->categories = $categories;
        
        return $this->categories;
    }

    /**
     * 
     * @param SimpleXmlElement $category
     * @param int $level
     * @param string $path
     * @return array
     */
    public function appendSubcategories($category, $level = 1, $path = null)
    {

        $prefix = '';
        $categories = [];

        foreach (range(0, $level) as $i) {
            $prefix .= "---";
        }

        if (isset($category->Subcategories)) {
            if ($level++ > 1) {
                $path .= '/' . (string) $category->Name;
            }
            $subcategories = $category->Subcategories->Category;
            foreach ($subcategories as $subcategory) {
                $categories[] = array('label' => $prefix . (string) $subcategory->Name, 'value' => $path . '/' . (string) $subcategory->Name);
                $categories = array_merge($categories, $this->appendSubcategories($subcategory, $level, $path));
            }
        }

        return $categories;
    }

}
