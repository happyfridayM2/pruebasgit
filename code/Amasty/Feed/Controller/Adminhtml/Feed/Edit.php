<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Edit
 *
 * @package Amasty\Feed
 */
class Edit extends \Amasty\Feed\Controller\Adminhtml\AbstractFeed
{
    /**
     * If Google Merchant Center rejects feed
     */
    const FAQ_LINK = 'https://amasty.com/knowledge-base/topic-product-feed-for-magento-2.html';

    /**
     * @var \Amasty\Feed\Model\Rule\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Amasty\Feed\Model\Schedule\Management
     */
    private $scheduleManagement;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Feed\Api\FeedRepositoryInterface
     */
    private $repository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Feed\Model\Schedule\Management $scheduleManagement,
        \Amasty\Feed\Model\Rule\RuleFactory $ruleFactory,
        \Amasty\Feed\Api\FeedRepositoryInterface $repository
    ) {
        $this->scheduleManagement = $scheduleManagement;
        $this->ruleFactory = $ruleFactory;

        parent::__construct($context);
        $this->registry = $registry;
        $this->repository = $repository;
    }

    public function execute()
    {
        if ($feedId = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->repository->getById($feedId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This feed no longer exists.'));

                return $this->_redirect('amfeed/*');
            }
        } else {
            $model = $this->repository->getEmptyModel();
        }

        /** @var \Amasty\Feed\Model\Rule\Rule $rule */
        $rule = $this->ruleFactory->create();

        $rule->setConditions([])
            ->setConditionsSerialized($model->getConditionsSerialized())
            ->getConditions()
            ->setJsFormObject('rule_conditions_fieldset');

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model = $this->scheduleManagement->prepareScheduleData($model);

        $this->registry->register('current_amfeed_feed', $model);
        $this->registry->register('current_amfeed_rule', $rule);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Feed::feed');
        $resultPage->addBreadcrumb(__('Amasty Feed'), __('Amasty Feed'));
        $resultPage->addBreadcrumb(__('Feed Edit'), __('Feed Edit'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getEntityId() ? $model->getName() : __('New Feed')
        );

        return $resultPage;
    }
}
