<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Model\Config\Source;

use Magento\Backend\Model\Auth\Session;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Module\Dir\Reader;

class GoogleCategory extends AbstractSource
{

    /**
     * @var string
     */
    const EXCLUDE_FROM_FEED_VALUE = '-1';

    /**
     * @var Reader
     */
    protected Reader $moduleReader;

    /**
     * @var Session $authSession
     */
    protected Session $authSession;

    /**
     * @var array
     */
    public $categories;

    /**
     * @param Reader $moduleReader
     * @param Session $authSession
     */
    public function __construct(
        Reader $moduleReader,
        Session $authSession
    ) {
        $this->moduleReader = $moduleReader;
        $this->authSession = $authSession;
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

        $locale = $this->authSession->getUser()->getInterfaceLocale();
        $googleTaxonomy = $this->getGoogleTaxonomy($locale);
        $categories = [['label' => __('Please select'), 'value' => ''], ['label' => __('Exclude from feed'), 'value' => self::EXCLUDE_FROM_FEED_VALUE]];

        $taxonomyArray = [];
        foreach ($googleTaxonomy as $taxonomyRow) {
            $taxonomyRow = explode(" - ", trim($taxonomyRow));
            if (isset($taxonomyRow[0]) && isset($taxonomyRow[1])) {
                $categories[] = ['label' => $taxonomyRow[1], 'value' => $taxonomyRow[0]];
            }
        }

        $this->categories = $categories;

        return $this->categories;
    }

    /**
     * @return array
     */
    public function getGoogleTaxonomy($locale = 'en_US')
    {
        $sourceDir = $this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
            'LCB_Feeds'
        );

        if (!file_exists($sourceDir . "/source/google/$locale.txt")) {
            $locale = 'en_US';
        }

        $taxonomyRaw = file_get_contents($sourceDir . "/source/google/$locale.txt", true);

        return (array) explode("\n", trim($taxonomyRaw));
    }
}
