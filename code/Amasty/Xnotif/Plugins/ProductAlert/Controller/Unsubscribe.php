<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\ProductAlert\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Amasty\Xnotif\Model\ResourceModel\Unsubscribe\AlertProvider;

/**
 * Class Unsubscribe
 */
class Unsubscribe
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Xnotif\Model\UrlHash
     */
    private $urlHash;

    /**
     * @var \Amasty\Xnotif\Model\ResourceModel\Unsubscribe\AlertProvider
     */
    private $alertProvider;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var null|\Magento\Customer\Model\Session
     */
    private $customerSession = null;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Xnotif\Model\UrlHash $urlHash,
        \Amasty\Xnotif\Model\ResourceModel\Unsubscribe\AlertProvider $alertProvider,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Customer\Model\SessionFactory $sessionFactory
    ) {
        $this->request = $request;
        $this->urlHash = $urlHash;
        $this->alertProvider = $alertProvider;
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->sessionFactory = $sessionFactory;
    }

    public function aroundDispatch(
        $subject,
        \Closure $proceed,
        RequestInterface $request
    ) {
        $productId = $this->request->getParam('product_id', $this->request->getParam('product'));
        if ($request->getActionName() == 'stockAll') {
            $productId = AlertProvider::REMOVE_ALL;
        }

        $subscribeConditions = [];
        if ($this->urlHash->check($this->request)) {
            $subscribeConditions['email'] = urldecode($this->request->getParam('email'));
        } else {
            $subscribeConditions['customer_id'] = $this->getCustomerSession()->getCustomerId();
        }

        $type = $this->request->getParam('type', $this->request->getActionName());

        try {
            $collection = $this->alertProvider->getAlertModel($type, $productId, $subscribeConditions);
            if ($collection && $collection->getSize()) {
                $collection->walk('delete');
            }
            if ($productId == AlertProvider::REMOVE_ALL) {
                $this->messageManager->addSuccessMessage(
                    __('You will no longer receive stock alerts.')
                );
            } else {
                $this->messageManager->addSuccessMessage(
                    __('You will no longer receive stock alerts for this product.')
                );
            }
        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage(__('The product was not found.'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl('/');

        return $resultRedirect;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        if ($this->customerSession === null) {
            $this->customerSession = $this->sessionFactory->create();
        }

        return $this->customerSession;
    }
}
