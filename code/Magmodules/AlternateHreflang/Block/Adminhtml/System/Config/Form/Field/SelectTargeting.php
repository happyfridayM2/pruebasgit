<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\DataObject;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class SelectTargeting
 *
 * @package Magmodules\AlternateHreflang\Block\Adminhtml\System\Config\Form\Field
 */
class SelectTargeting extends AbstractFieldArray
{

    /**
     * @var
     */
    private $storesRenderer;
    /**
     * @var
     */
    private $groupsRenderer;

    /**
     * Add button label
     */
    public function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add Alternate');
    }

    /**
     * Render block
     */
    public function _prepareToRender()
    {
        $this->addColumn('store_id', [
            'label'    => __('Storefront'),
            'renderer' => $this->getStoresRenderer()
        ]);
        $this->addColumn('language_code', [
            'label' => __('Language Code')
        ]);
        $this->addColumn('group_id', [
            'label'    => __('Group'),
            'renderer' => $this->getGroupsRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Alternate');
    }

    /**
     * Returns render of stores
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getStoresRenderer()
    {
        if (!$this->storesRenderer) {
            try {
                $this->storesRenderer = $this->getLayout()->createBlock(
                    '\Magmodules\AlternateHreflang\Block\Adminhtml\System\Config\Form\Field\Stores',
                    '',
                    ['data' => ['is_render_to_js_template' => true]]
                );
            } catch (\Exception $e) {
                $this->storesRenderer = [];
            }
        }

        return $this->storesRenderer;
    }

    /**
     * Returns render of groups
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getGroupsRenderer()
    {
        if (!$this->groupsRenderer) {
            try {
                $this->groupsRenderer = $this->getLayout()->createBlock(
                    '\Magmodules\AlternateHreflang\Block\Adminhtml\System\Config\Form\Field\Groups',
                    '',
                    ['data' => ['is_render_to_js_template' => true]]
                );
            } catch (\Exception $e) {
                $this->groupsRenderer = [];
            }
        }

        return $this->groupsRenderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     */
    public function _prepareArrayRow(DataObject $row)
    {
        $storeId = $row->getdata('store_id');
        $groupId = $row->getdata('group_id');
        $options = [];
        if ($storeId) {
            /** @noinspection PhpUndefinedMethodInspection */
            $options['option_' . $this->getStoresRenderer()->calcOptionHash($storeId)] = 'selected="selected"';
        }
        if ($groupId) {
            /** @noinspection PhpUndefinedMethodInspection */
            $options['option_' . $this->getGroupsRenderer()->calcOptionHash($groupId)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @param string $columnName
     *
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == "language_code") {
            $this->_columns[$columnName]['class'] = 'input-text required-entry';
            $this->_columns[$columnName]['style'] = 'width:150px';
        }
        if ($columnName == "group_id") {
            $this->_columns[$columnName]['style'] = 'width:150px';
        }
        return parent::renderCellTemplate($columnName);
    }
}
