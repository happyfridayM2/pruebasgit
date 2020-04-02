<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class NewAction
 */
class NewAction extends Action
{
    /**
     * @var ForwardFactory
     */
    private $forwardFactory;

    public function __construct(Action\Context $context, ForwardFactory $forwardFactory)
    {
        parent::__construct($context);
        $this->forwardFactory = $forwardFactory;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        return $this->forwardFactory->create()
            ->forward('edit');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Xnotif::subscription');
    }
}
