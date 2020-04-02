<?php

/**
 * Copyright © 2017 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrderEraser\Ui\Component\Listing\Column;

/**
 * Render column block in the order grid
 */
class Delete extends \Magento\Ui\Component\Listing\Columns\Column
{

    private $_urlInterface = null;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        array $components = [],
        array $data = []
    ) {
        $this->_urlInterface = $urlInterface;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     *
     * @param array $dataSource
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $html = "<a href='#' onclick='javascript:jQuery(this).parents(\"TD\").eq(0).off(\"click\");";
                $html .= "OrderEraser.delete(\"";
                $html .= $this->_urlInterface->getUrl('ordereraser/orders/delete', array('selected' => $item['entity_id']));
                $html .= "\");";
                $html .= "'>Delete</a></span>";
                $item[$this->getData('name')] = $html;
            }
        }
        return $dataSource;
    }
}