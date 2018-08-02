<?php
namespace LCB\Feeds\Controller;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

abstract class Feed extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

   
}

