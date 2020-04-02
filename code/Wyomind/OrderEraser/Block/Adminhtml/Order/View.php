<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrderEraser\Block\Adminhtml\Order;

/**
 * Render the export button in order > view
 */
class View
{
    private $_urlInterface;

    /**
     *
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(\Magento\Framework\UrlInterface $urlInterface)
    {
        $this->_urlInterface = $urlInterface;
    }
    
    /**
     * Interceptor for getOrder
     * @param \Magento\Sales\Block\Adminhtml\Order\View $subject
     */
    public function beforeGetOrder(
        \Magento\Sales\Block\Adminhtml\Order\View $subject
    ) {

        $subject->addButton(
            'void_delete',
            array(
            'label' => __('Delete'),
            'onclick' => 'setLocation(\'' . $this->_urlInterface->getUrl('ordereraser/orders/delete', array('selected' => $subject->getRequest()->getParam('order_id'))) . '\')'
            )
        );
    }
}