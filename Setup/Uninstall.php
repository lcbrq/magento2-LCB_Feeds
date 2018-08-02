<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements \Magento\Framework\Setup\UninstallInterface
{

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(
            \Magento\Framework\Setup\SchemaSetupInterface $setup, 
            \Magento\Framework\Setup\ModuleContextInterface $context
        )
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'ceneo_category');
        $setup->endSetup();
    }

}
