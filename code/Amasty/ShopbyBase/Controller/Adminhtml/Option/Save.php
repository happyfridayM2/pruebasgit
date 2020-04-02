<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Controller\Adminhtml\Option;

use Amasty\ShopbyBase\Model\Cache\Type;
use Magento\Backend\App\Action;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Save extends \Amasty\ShopbyBase\Controller\Adminhtml\Option
{
    /**
     * @var  TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        TypeListInterface $typeList,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->cacheTypeList = $typeList;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $filterCode = $this->getRequest()->getParam('filter_code');
        $optionId = $this->getRequest()->getParam('option_id');
        $storeId = $this->getRequest()->getParam('store', 0);
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                /** @var \Amasty\ShopbyBase\Model\OptionSetting $model */
                $model = $this->_objectManager->create(\Amasty\ShopbyBase\Model\OptionSetting::class);
                $data = $this->filterData($data);

                $model->saveData($filterCode, $optionId, $storeId, $data);

                $this->cacheTypeList->invalidate(Type::TYPE_IDENTIFIER);
                $this->messageManager->addSuccessMessage(__('You saved the item.'));
                $this->_session->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit',
                        [
                            'filter_code' => $filterCode,
                            'option_id' => $optionId,
                            'store' => $storeId
                        ]
                    );

                    return;
                }
                $this->_redirectRefer();
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirectRefer();
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_session->setPageData($data);
                $this->_redirectRefer();
                return;
            }
        }
        $this->_redirectRefer();
    }

    protected function _redirectRefer()
    {
        $this->_forward('settings');
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    protected function filterData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            [],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();

        return $data;
    }
}
