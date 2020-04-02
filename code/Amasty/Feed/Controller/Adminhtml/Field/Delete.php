<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Field;

use Amasty\Feed\Controller\Adminhtml\AbstractField;

/**
 * Class Delete
 *
 * @package Amasty\Feed
 */
class Delete extends AbstractField
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
        if ($fieldId = $this->getRequest()->getParam('id')) {
            try {
                $this->repository->deleteAllConditions($fieldId, true);

                $this->messageManager->addSuccessMessage(__('You deleted the field.'));

                return $this->_redirect('amfeed/*/');
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('We can\'t delete the field right now. Please review the log and try again.')
                );

                return $this->_redirect('amfeed/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a field to delete.'));

        return $this->_redirect('amfeed/*/');
    }
}
