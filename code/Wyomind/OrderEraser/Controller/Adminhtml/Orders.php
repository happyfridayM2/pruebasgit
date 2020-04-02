<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderEraser\Controller\Adminhtml;

abstract class Orders extends \Magento\Backend\App\Action
{

    public $ordersCollectionFactory = null;
    public $searchCriteriaBuilder = null;
    public $orderRepository = null;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Wyomind\OrderEraser\Model\ResourceModel\Orders\CollectionFactory $ordersCollectionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->ordersCollectionFactory = $ordersCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }
    
    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_OrderEraser::delete');
    }

    abstract public function execute();
}