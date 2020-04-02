<?php

namespace Connectif\Integration\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

class BannerConfig extends AbstractFieldArray
{
    /**
     * @var $_bannerBlockRenderer BannerConfig
     */
    protected $_bannerBlockRenderer;

    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Get Banner options
     */
    protected function _getBannerBlockNameRenderer()
    {
        if (!$this->_bannerBlockRenderer) {
            $this->_bannerBlockRenderer = $this->getLayout()->createBlock(
                'Connectif\Integration\Block\Adminhtml\BannerBlock',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_bannerBlockRenderer;
    }

    /**
     * Prepare to render.
     *
     * @return void
     */
    public function _prepareToRender()
    {
        $this->addColumn('block_name', array(
            'label' => 'Block Name',
            'renderer' => $this->_getBannerBlockNameRenderer(),
        ));
        $this->addColumn('banner_id', array(
            'label' => 'Banner Id',
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
        $customAttribute = $row->getData('block_name');

        $key = 'option_' . $this->_getBannerBlockNameRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected="selected"';
        $row->setData('option_extra_attrs', $options);
    }
}
