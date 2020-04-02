<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Controller\Adminhtml\Group;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends \Amasty\Conf\Controller\Adminhtml\Group
{
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $decoder;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amasty\Conf\Model\GroupAttrFactory $groupAttrFactory,
        \Amasty\Conf\Model\GroupAttrRepository $groupAttrRepository,
        \Magento\Backend\Model\SessionFactory $sessionFactory,
        \Magento\Framework\Json\DecoderInterface $decoder,
        JsonFactory $jsonFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $resultPageFactory,
            $groupAttrFactory,
            $groupAttrRepository,
            $sessionFactory
        );
        $this->decoder = $decoder;
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);

        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach ($postItems as $key => $item) {
            $id = (int)$item['group_id'];
            $model = $this->groupAttrRepository->get($id);

            if (!$model->getId() && $id) {
                return $resultJson->setData([
                    'messages' => [__('This group no longer exists.')],
                    'error' => true,
                ]);
            }

            try {
                $options = $item;
                if (!is_array($item['option'])) {
                    $options['option'] = $this->decoder->decode($item['option']);
                }

                $model->setData($this->beforeSetData($options));
                $this->groupAttrRepository->save($model);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $e->getMessage();
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $e->getMessage();
                $error = true;
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                $messages[] = __('Something went wrong while saving the group.');
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param $data
     * @return mixed
     */
    private function beforeSetData($data)
    {
        if (isset($data['option'])) {
            $data['attribute_options'] = [];
            $data['attribute_values'] = [];
            foreach ($data['option'] as $value) {
                if (isset($value['checked']) && $value['checked']) {
                    $data['attribute_' . $value['type_group'] . 's'][$value['id']] = [
                        'is_active' => ['value' => $value['value']],
                        'sort_order' => ['value' => $value['sort_order']]
                    ];
                }
            }
            unset($data['option']);
        }

        return $data;
    }
}
