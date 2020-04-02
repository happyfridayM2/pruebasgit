<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Field;

use Amasty\Feed\Controller\Adminhtml\AbstractField;
use Magento\Framework\Controller\ResultFactory;

class Edit extends AbstractField
{
    /**
     * @var \Amasty\Feed\Api\CustomFieldsRepositoryInterface
     */
    private $repository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Feed\Api\CustomFieldsRepositoryInterface $repository
    ) {
        $this->repository = $repository;

        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Feed::feed_field');
        $resultPage->getConfig()->getTitle()->prepend(__('New Condition-Based Attribute'));

        if ($idField = $this->getRequest()->getParam('id')) {
            /** @var \Amasty\Feed\Model\Field $model */
            $model = $this->repository->getFieldModel($idField);

            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Condition-Based Attribute no longer exists.'));

                return $this->_redirect('amfeed/*');
            }
            $resultPage->getConfig()->getTitle()->prepend($model->getName());
        }

        return $resultPage;
    }
}
