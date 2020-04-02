<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Block\Adminhtml\Page;

use Magento\Framework\View\Element\Template;
use Amasty\ShopbyPage\Controller\RegistryConstants;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

/**
 * @api
 */
class Selection extends Template
{
    /** @var Registry */
    protected $_coreRegistry;

    public function __construct(
        Context $context,
        Registry $registry,
        $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'selection.phtml';

    /**
     * Get attribute values url
     * @return string
     */
    public function getSelectionUrl()
    {
        return $this->getUrl('amasty_shopbypage/page/selection');
    }

    /**
     * Get add attribute values row url
     * @return string
     */
    public function getAddSelectionUrl()
    {
        return $this->getUrl('amasty_shopbypage/page/addSelection');
    }

    /**
     * @return int
     */
    public function getCounter()
    {
        /** @var \Amasty\ShopbyPage\Model\Page $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::PAGE);
        $conditions = $model->getConditions();

        return $conditions && !is_string($conditions) ? count($conditions) : 0;
    }
}
