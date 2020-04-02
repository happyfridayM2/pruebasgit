<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Controller\Adminhtml\AbstractFeed;
use Amasty\Feed\Model\Rule\Rule;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 *
 * @package Amasty\Feed
 */
class Save extends AbstractFeed
{
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var \Amasty\Feed\Model\Rule\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Amasty\Feed\Model\FeedRepository
     */
    private $feedRepository;

    /**
     * @var \Amasty\Feed\Model\Schedule\Management
     */
    private $scheduleManagement;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Feed\Model\Rule\RuleFactory $ruleFactory,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\Feed\Model\Schedule\Management $scheduleManagement,
        \Amasty\Feed\Model\FeedRepository $feedRepository,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        parent::__construct($context);

        $this->ruleFactory = $ruleFactory;
        $this->serializer = $serializer;
        $this->scheduleManagement = $scheduleManagement;
        $this->feedRepository = $feedRepository;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
    }

    /**
     * @return FeedInterface
     *
     * @throws LocalizedException
     */
    public function save($data)
    {
        /** @var FeedInterface $model */
        $model = $this->feedRepository->getEmptyModel();

        if ($feedId = $this->getRequest()->getParam('feed_entity_id')) {
            /** @var FeedInterface $model */
            $model = $this->feedRepository->getById($feedId);
        }

        if ($data['feed_type'] === 'xml') {
            if ((!isset($data['xml_header']) || !$data['xml_header'])
                && (!isset($data['xml_footer']) || !$data['xml_footer'])
            ) {
                $data['xml_header'] = '<?xml version="1.0"?>'
                    . '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"> <channel>'
                    . '<created_at>{{DATE}}</created_at>';
                $data['xml_footer'] = '</channel> </rss>';
            }
        }

        if (isset($data['feed_entity_id'])) {
            $data['entity_id'] = $data['feed_entity_id'];
        }

        if (isset($data['store_ids'])) {
            $data['store_ids'] = implode(",", $data['store_ids']);
        }

        if (isset($data['csv_field'])) {
            $data['csv_field'] = $this->serializer->serialize($data['csv_field']);
        }

        if (isset($data['rule']) && isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];

            unset($data['rule']);

            /** @var Rule $rule */
            $rule = $this->ruleFactory->create();
            $rule->loadPost($data);

            $data['conditions_serialized'] = $this->serializer->serialize($rule->getConditions()->asArray());
            unset($data['conditions']);
        }

        if (isset($data['entity_id']) && $data['entity_id'] === "") {
            $data['entity_id'] = null;
        }

        if (isset($data[FeedInterface::DELIVERY_PASSWORD])) {
            if (preg_match('/^\*+$/', $data[FeedInterface::DELIVERY_PASSWORD])) {
                unset($data[FeedInterface::DELIVERY_PASSWORD]);
            } else {
                $data[FeedInterface::DELIVERY_PASSWORD] = $this->encryptor->encrypt(
                    $data[FeedInterface::DELIVERY_PASSWORD]
                );
            }
        }

        $model->setData($data);

        $this->_session->setPageData($model->getData());

        $this->feedRepository->save($model, true);

        $this->scheduleManagement->saveScheduleData($model->getEntityId(), $data);

        $this->_session->setPageData(false);

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $data = $this->getRequest()->getPostValue();

            $model = $this->save($data);
            $this->messageManager->addSuccessMessage(__('You saved the feed.'));

            if ($this->getRequest()->getParam('back')) {
                return $this->_redirect('amfeed/feed/edit', ['id' => $model->getId()]);
            }

            return $this->_redirect('amfeed/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = (int)$this->getRequest()->getParam('feed_entity_id');

            if (!empty($id)) {
                return $this->_redirect('amfeed/*/edit', ['id' => $id]);
            } else {
                return $this->_redirect('amfeed/*/new');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the feed data. Please review the error log.')
            );
            $this->logger->critical($e);
            $this->_session->setPageData($data);
            return $this->_redirect('amfeed/*/edit', ['id' => $this->getRequest()->getParam('feed_entity_id')]);
        }
    }
}
