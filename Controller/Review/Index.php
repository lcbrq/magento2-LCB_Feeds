<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Controller\Review;

class Index extends \LCB\Feeds\Controller\Feed
{

    /**
     * Ceneo feed action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->getResponse()->setHeader('Content-Type', 'text/xml', true);
        $this->getResponse()->setHeader('X-Magento_Tags', 'FEEDS');

        if ($feed = $this->getFromCache('reviews')) {
            return $this->getResponse()->setBody($feed);
        }

        $feed = $this->resultPageFactory->create()->getLayout()
                ->createBlock('LCB\Feeds\Block\Review')
                ->setTemplate('LCB_Feeds::reviews.phtml')
                ->toHtml();

        $this->getResponse()->setBody($feed);
    }
}
