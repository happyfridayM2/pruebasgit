<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Action;

/**
 * Class FrontControllerActionPredispatch
 * @package Amasty\ShopbySeo\Observer
 */
class FrontControllerActionPredispatch implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var array
     */
    private $suffixModules = ['catalog', 'amshopby', 'ambrand'];

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    private $actionFlag;

    /**
     * @var \Amasty\ShopbySeo\Helper\Url
     */
    private $urlHelper;

    public function __construct(
        RequestInterface $request,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Amasty\ShopbySeo\Helper\Url $urlHelper
    ) {
        $this->request = $request;
        $this->actionFlag = $actionFlag;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->request->getMetaData(\Amasty\ShopbySeo\Helper\Data::SEO_REDIRECT_FLAG)
            && $this->request->getModuleName()
        ) {
            $this->request->setDispatched(true);
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
        } elseif ($this->request->getMetaData(\Amasty\ShopbySeo\Helper\Data::SEO_REDIRECT_MISSED_SUFFIX_FLAG)
            && $this->urlHelper->isAddSuffixToShopby()
            && in_array($this->request->getModuleName(), $this->suffixModules)
        ) {
            $this->request->setMetaData(\Amasty\ShopbySeo\Helper\Data::SEO_REDIRECT_FLAG, true);
            $this->request->setDispatched(true);
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
        }
        return $this;
    }
}
