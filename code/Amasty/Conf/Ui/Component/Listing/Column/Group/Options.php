<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Ui\Component\Listing\Column\Group;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Options extends Column
{
    /**
     * @var \Magento\Framework\Json\Encoder
     */
    private $encoder;

    /**
     * @var \Amasty\Conf\Model\ResourceModel\GroupAttr
     */
    private $resourceGroupAttr;

    /**
     * Options constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Amasty\Conf\Model\ResourceModel\GroupAttr $resourceGroupAttr
     * @param \Magento\Framework\Json\Encoder $encoder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Amasty\Conf\Model\ResourceModel\GroupAttr $resourceGroupAttr,
        \Magento\Framework\Json\Encoder $encoder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->encoder = $encoder;
        $this->resourceGroupAttr = $resourceGroupAttr;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    private function prepareItem(array $item)
    {
        list($items, $object) = $this->resourceGroupAttr->getOptions($item);
        $array = ['items' => $items, 'code' => $item['attribute_id'], 'type' => $object];
        return $this->encoder->encode($array);
    }
}
