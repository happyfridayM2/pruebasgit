<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Renderer;

/**
 * Render the link in the profile grid
 */
class Link extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Wyomind\OrdersExportTool\Helper\Storage|null
     */
    protected $_storageHelper = null;

    /**
     * @param \Magento\Backend\Block\Context           $context
     * @param \Wyomind\OrdersExportTool\Helper\Storage $storageHelper
     * @param array                                    $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Wyomind\OrdersExportTool\Helper\Storage $storageHelper,
        array $data = []
    )
    {
        $this->_storageHelper = $storageHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render the column block
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $file = $this->_storageHelper->getFile($row);
        if (file_exists(BP . "/" . $file)) {
            return sprintf('<a href="%1$s?r=' . time() . '" target="_blank">%1$s</a>', $this->_storageHelper->getFileUrl($file));
        }

        return '-';
    }
}