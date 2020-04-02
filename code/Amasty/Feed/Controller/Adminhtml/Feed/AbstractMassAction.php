<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class AbstractMassAction
 *
 * @package Amasty\Feed
 */
abstract class AbstractMassAction extends \Amasty\Feed\Controller\Adminhtml\AbstractFeed
{
    /**
     * @var \Amasty\Feed\Model\Feed\Copier
     */
    public $feedCopier;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * @var \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory
     */
    public $collectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\Feed\Copier $feedCopier,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);

        $this->feedCopier = $feedCopier;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            if ($ids = $this->getRequest()->getParam('selected')) {
                /** @var \Amasty\Feed\Model\ResourceModel\Feed\Collection $collection */
                $collection  = $this->getCollection()->addFieldToFilter(
                    'entity_id',
                    ['in' => implode(',', $ids)]
                );
                if (!$collection->getSize()) {
                    throw new LocalizedException(__('This feed no longer exists.'));
                }
                $this->massAction($collection);
            } else {
                $collection = $this->getCollection();
                if (!$collection->getSize()) {
                    throw new LocalizedException(__('This feed no longer exists.'));
                }
                $this->massAction($collection);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong. Please review the error log.')
            );
            $this->logger->critical($e);
        }

        $this->_redirect('amfeed/*/index');
    }

    /**
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    private function getCollection()
    {
        return $this->filter->getCollection(
            $this->collectionFactory->create()->addFieldToFilter('is_template', ['neq' => 1])
        );
    }

    abstract public function massAction($collection);
}
