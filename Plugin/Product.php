<?php

namespace LCB\Feeds\Plugin;

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */
class Product
{

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
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->escaper = $escaper;
        $this->filterManager = $filterManager;
    }

    /**
     * Get escaped product name
     *
     * @param LCB\Feeds\Model\Product $product
     * @param string $name
     * @return string
     */
    public function afterGetName(\LCB\Feeds\Model\Product $product, $name)
    {
        return $this->escaper->escapeHtml(preg_replace('/[\x00-\x1F\x7F]/', '', $name));
    }

    /**
     * Get escaped product description limited to 5000 chars
     *
     * @param LCB\Feeds\Model\Product $product
     * @param string $description
     * @return string
     */
    public function afterGetDescription(\LCB\Feeds\Model\Product $product, $description)
    {
        return $this->filterManager->truncate($this->escaper->escapeHtml($description), ['length' => 5000]);
    }
}
