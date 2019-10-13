<?php

namespace LCB\Feeds\Block\Cache;

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */
class Additional extends \Magento\Backend\Block\Cache\Additional
{

    /**
     * @return string
     */
    public function getCleanFeedsUrl()
    {
        return $this->getUrl('feeds/cache/generateFeeds', ['form_key' => $this->getFormKey()]);
    }
}
