<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Controller\Adminhtml\Page;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry as CoreRegistry;
use Amasty\ShopbyPage\Controller\RegistryConstants;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Selection
 *
 * @package Amasty\ShopbyPage\Controller\Adminhtml\Page
 */
class Selection extends Action
{
    /**
     * Core registry
     *
     * @var CoreRegistry
     */
    protected $_coreRegistry = null;

    /**
     * @var CatalogConfig
     */
    protected $_catalogConfig;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        CatalogConfig $catalogConfig,
        CoreRegistry $registry
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShopbyPage::page');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $attribute = $this->loadAttribute();
            $attributeIdx = $this->getRequest()->getParam('idx');
            if (isset($attributeIdx)) {
                $attributeIdx = (int) $attributeIdx;
            }

            $this->_coreRegistry->register(RegistryConstants::ATTRIBUTE, $attribute);
            $this->_coreRegistry->register(RegistryConstants::ATTRIBUTE_IDX, $attributeIdx);

            return $this->_resultPageFactory->create();
        } catch (LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => $e->getMessage() . __('We can\'t fetch attribute options.')];
        }

        $resultJson = $this->_resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @throws LocalizedException
     */
    private function loadAttribute()
    {
        $attributeId = (int) $this->getRequest()->getParam('id');
        $attribute = $this->_catalogConfig->getAttribute(Product::ENTITY, $attributeId);

        if (!$attribute->getId()) {
            throw new LocalizedException(__('Attribute does n\'t exists'));
        }

        return $attribute;
    }
}
