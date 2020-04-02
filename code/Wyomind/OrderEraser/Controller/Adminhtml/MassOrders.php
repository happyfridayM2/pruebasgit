<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderEraser\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Wyomind\OrderEraser\Model\ResourceModel\Orders\CollectionFactory as OECollectionFactory;

abstract class MassOrders extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{

    public $collectionFactory = null;
    public $oeCollectionFactory = null;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context,
            Filter $filter,
            CollectionFactory $collectionFactory,
            OECollectionFactory $oeCollectionFactory)
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->oeCollectionFactory = $oeCollectionFactory;
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Wyomind_OrderEraser::delete');
    }

}
