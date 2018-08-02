<?php

namespace LCB\Feeds\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{

    private $eavSetupFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.1') < 0) {

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Category::ENTITY, 'ceneo_category', [
                        'type' => 'int',
                        'label' => 'Ceneo Category',
                        'input' => 'select',
                        'required' => false,
                        'sort_order' => 949,
                        'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                        'wysiwyg_enabled' => true,
                        'is_html_allowed_on_front' => true,
                        'group' => 'General Information',
                    ]
            );
        }

        $setup->endSetup();
    }

}
