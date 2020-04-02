<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Field;

use Amasty\Feed\Controller\Adminhtml\AbstractField;
use Amasty\Feed\Model\Field\Field;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 *
 * @package Amasty\Feed
 */
class MassDelete extends AbstractField
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

    /**
     * @var \Amasty\Feed\Api\CustomFieldsRepositoryInterface
     */
    private $repository;

    /**
     * @var \Amasty\Feed\Model\Field\ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Amasty\Feed\Api\CustomFieldsRepositoryInterface $repository,
        \Amasty\Feed\Model\Field\ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->repository = $repository;

        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $result->setPath('*/*/index');

        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $recordDeleted = 0;

        foreach ($collection->getData() as $record) {
            $this->repository->deleteAllConditions($record[Field::FEED_FIELD_ID], true);
            $recordDeleted++;
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $recordDeleted));

        return $result;
    }
}
