<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Ui\Component\Listing\Column;

use Amasty\Feed\Model\Config\Source\FeedStatus;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Generated extends \Magento\Ui\Component\Listing\Columns\Date
{
    /**#@+
     * Attributes to column
     */
    const READY_ATTRIBUTES = [
        'generated_at' => 'Date',
        'generation_type' => 'Executed',
        'products_amount' => 'Products'
    ];

    const PROCESSING_ATTRIBUTES = [
        'products_amount' => 'Products'
    ];

    const DEFAULT_ATTRIBUTE = [
        'status' => 'Status',
    ];

    /**
     * @var FeedStatus
     */
    private $feedStatus;

    /**#@-*/

    public function __construct(
        FeedStatus $feedStatus,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        BooleanUtils $booleanUtils,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $timezone, $booleanUtils, $components, $data);
        $this->feedStatus = $feedStatus;
    }

    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource);

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item['orig_' . $this->getData('name')] = $item[$this->getData('name')];
                $item[$this->getData('name')] = $this->getColumnValue($item);
            }
        }

        return $dataSource;
    }

    /**
     * @param array $item
     *
     * @return string
     */
    private function getColumnValue($item)
    {
        $result = '';
        $columns = self::DEFAULT_ATTRIBUTE;

        switch ($item['status']) {
            case FeedStatus::READY:
                $columns += self::READY_ATTRIBUTES;
                break;
            case FeedStatus::PROCESSING:
                $columns += self::PROCESSING_ATTRIBUTES;
                break;
        }

        /** @var \Amasty\Feed\Ui\DataProvider\Feed\FeedDataProvider $dataProvider */
        $dataProvider = $this->getContext()->getDataProvider();
        $item['status'] = $this->feedStatus->toArray()[$item['status']];

        foreach ($columns as $key => $value) {
            $result .= $dataProvider->getEscaper()->escapeHtml(__($value)) . " : "
                . $dataProvider->getEscaper()->escapeHtml(__($item[$key])) . "<br/>";
        }

        return $result;
    }
}
