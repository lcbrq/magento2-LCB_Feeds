<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Controller\Facebook;

class Index extends \LCB\Feeds\Controller\Feed
{

    /**
     * Facebook feed action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->getResponse()->setHeader('Content-Type', 'text/xml', true);
        $this->getResponse()->setHeader('X-Magento_Tags', 'FEEDS');

        if ($feed = $this->getFromCache('facebook')) {
            return $this->getResponse()->setBody($feed);
        }

        $feed = $this->resultPageFactory->create()->getLayout()
                ->createBlock('LCB\Feeds\Block\Product')
                ->setTemplate('LCB_Feeds::facebook.phtml')
                ->toHtml();

        $this->getResponse()->setBody($feed);
    }
}
