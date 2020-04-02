<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Field;

use Amasty\Feed\Block\Adminhtml\Field\Edit\Conditions;
use Amasty\Feed\Controller\Adminhtml\AbstractField;

class Save extends AbstractField
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Amasty\Feed\Api\CustomFieldsRepositoryInterface
     */
    private $repository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Amasty\Feed\Api\CustomFieldsRepositoryInterface $repository
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->repository = $repository;

        parent::__construct($context);
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                /** @var \Amasty\Feed\Model\Field\Field $model */
                $model = $this->repository->getFieldModel();
                $model->setData($data);
                $this->repository->saveField($model);
                $this->repository->deleteAllConditions($model->getId());
                $this->saveCondition($data, $model->getId());
                $this->saveCondition($data, $model->getId(), 'default');
                $this->messageManager->addSuccessMessage(__('Saved successfully'));
                $this->dataPersistor->clear(Conditions::FORM_NAMESPACE);

                if (!$this->getRequest()->getParam('back')) {
                    return $this->_redirect('amfeed/*/');
                }
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Condition-Based Attribute with this code are exist. Please, choose another code name.')
                );

                $this->dataPersistor->set(Conditions::FORM_NAMESPACE, $data);

                if (!isset($data['feed_field_id'])) {
                    return $this->_redirect('amfeed/*/new');
                }
            }

            return $this->_redirect('amfeed/field/edit', ['id' => $model->getId()]);
        }

        return $this->_redirect('amfeed/*/');
    }

    /**
     * Save condition block
     * Default block should be saved last
     *
     * @param array $data
     * @param string $block
     */
    private function saveCondition(&$data, $feedId, $block = 'rule')
    {
        $model = $this->repository->getConditionModel();

        if (isset($data[$block])) {
            $model->loadPost($data[$block]);
        }

        $this->repository->saveCondition($model, $feedId);
    }
}
