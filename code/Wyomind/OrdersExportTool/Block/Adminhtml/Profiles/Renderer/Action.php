<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $actions = [
            [// Edit
                'caption' => __('Edit'),
                'url' => [
                    'base' => '*/*/edit'
                ],
                'field' => 'id'
            ],
            [// Generate
                'caption' => __('Generate'),
                'url' => "javascript:void(require(['oet_index'], function (index) { index.generate('" . $this->getUrl('ordersexporttool/profiles/generate', ['id' => $row->getId()]) . "'); }))"
            ],
            [// Preview
                'caption' => __('Preview'),
                'url' => [
                    'base' => '*/*/preview'
                ],
                'field' => 'id',
                'popup' => true
            ],
            [// Delete
                'caption' => __('Delete'),
                'url' => "javascript:void(require(['oet_index'], function (index) { index.delete('" . $this->getUrl('ordersexporttool/profiles/delete', ['id' => $row->getId()]) . "'); }))"
            ]
        ];

        $this->getColumn()->setActions($actions);

        return parent::render($row);
    }
}