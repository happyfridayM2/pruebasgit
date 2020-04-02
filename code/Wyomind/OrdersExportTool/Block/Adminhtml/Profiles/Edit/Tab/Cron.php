<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Cron tab
 */
class Cron extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_helper = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Wyomind\OrdersExportTool\Helper\Data   $helper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Wyomind\OrdersExportTool\Helper\Data $helper,
        array $data = []
    )
    {
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('profile');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('');
        $form->setValues($model->getData());
        $this->setForm($form);

        $this->setTemplate('profiles/edit/cron.phtml');

        return parent::_prepareForm();
    }

    /**
     * Return tab label
     * @return string
     */
    public function getTabLabel()
    {
        return __('Cron schedule');
    }

    /**
     * Return tab title
     * @return string
     */
    public function getTabTitle()
    {
        return __('Cron schedule');
    }

    /**
     * can show tab
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Is visible
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
    
    public function getScheduledTask()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model->getScheduledTask();
    }
}