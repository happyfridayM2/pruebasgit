<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Abbacus\Catalog\Controller\Order;

use Magento\Framework\App\Action\Context;

class Email extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
 
    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $email = 'mayank.abbacus@gmail.com';
        $email = 'mayank@webtechsystem.com';
        $orderId = 4769;
        $orderId = 1280;
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId); // this is entity id
        $order->setCustomerEmail($email);
        if ($order) {
            try {
                $this->_objectManager->create('\Magento\Sales\Model\OrderNotifier')
                    ->notify($order);
                echo (__('You sent the order email.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                echo ($e->getMessage());
            } catch (\Exception $e) {
                echo (__('We can\'t send the email order right now.'));
                $this->_objectManager->create('Magento\Sales\Model\OrderNotifier')->critical($e);
            }
        } else {
            echo 'Order not found!';
        }
        exit;
    }
}