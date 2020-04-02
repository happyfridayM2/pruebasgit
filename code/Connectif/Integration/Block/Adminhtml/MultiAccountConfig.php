<?php

namespace Connectif\Integration\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class MultiAccountConfig extends AbstractFieldArray
{
    /**
     * @var $_multiAccountRenderer MultiAccountConfig
     */
    protected $_multiAccountBlockRenderer;
    protected $_currencyItemRenderer;

    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Get Multi Account options
     */
    protected function _getActivateAccountRenderer()
    {
        if (!$this->_multiAccountBlockRenderer) {
            $this->_multiAccountBlockRenderer = $this->getLayout()->createBlock(
                'Connectif\Integration\Block\Adminhtml\MultiAccountBlock',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_multiAccountBlockRenderer;
    }


    protected function _getCurrencyRenderer()
    {
        if (!$this->_currencyItemRenderer) {
            $this->_currencyItemRenderer = $this->getLayout()->createBlock(
                'Connectif\Integration\Block\Adminhtml\CurrencyBlock',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_currencyItemRenderer;
    }

    /**
     * Prepare to render.
     *
     * @return void
     */
    public function _prepareToRender()
    {
        $this->addColumn('is_active', array(
            'label' => 'Activate account',
            'renderer' => $this->_getActivateAccountRenderer(),
            'style' => 'width:80px',
        ));
        $this->addColumn('currency_id', array(
            'label' => 'Currency',
            'renderer' => $this->_getCurrencyRenderer(),
            'style' => 'width:80px',
        ));
        $this->addColumn('client_id', array(
            'label' => 'Client ID',
            'style' => 'width:100px',
        ));
        $this->addColumn('secret_key', array(
            'label' => 'Secret Key',
            'style' => 'width:100px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = 'Add';
    }

    /**
     * Prepare existing row data object.
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        $customAttribute = $row->getData('currency_id');
        $key = 'option_' . $this->_getCurrencyRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected="selected"';
        $row->setData('option_extra_attrs', $options);

        $options = [];
        $customAttribute = $row->getData('is_active');
        $key = 'option_' . $this->_getActivateAccountRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected="selected"';
        $row->setData('option_extra_attrs', $options);
    }
}
