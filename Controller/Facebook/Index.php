<?php

/**
 * Customizable datafeeds extension for Magento 2
 *
 * @category   LCB
 * @package    LCB_Feeds
 * @author     Silpion Tomasz Gregorczyk <tom@leftcurlybracket.com>
 */

namespace LCB\Feeds\Controller\Facebook;

use LCB\Feeds\Controller\Feed;

class Index extends Feed
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
            $feed = $this->adjustOutput($feed);
            return $this->getResponse()->setBody($feed);
        }

        $feed = $this->resultPageFactory->create()->getLayout()
                ->createBlock('LCB\Feeds\Block\Product')
                ->setTemplate('LCB_Feeds::facebook.phtml')
                ->toHtml();

        $feed = $this->adjustOutput($feed);
        $this->getResponse()->setBody($feed);
    }

    /**
     * @param string $feed
     * @return string
     */
    private function adjustOutput($feed)
    {
        $simpleXml = simplexml_load_string($feed);
        $dom = new \DOMDocument("1.0");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($simpleXml->asXML());

        return $dom->saveXML();
    }
}
