<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Source\Attribute;

use Magento\Framework\Option\ArrayInterface;

class Option implements ArrayInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $swatchesOptions;

    /**
     * @var int
     * */
    private $skipAttributeId;

    /**
     * @var \Magento\Swatches\Model\SwatchFactory
     */
    private $swatchFactory;

    /**
     * @var \Magento\Swatches\Helper\Media
     */
    private $swatchHelper;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler
     */
    private $configurableAttributeHandler;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    private $collection;

    public function __construct(
        \Magento\Swatches\Model\SwatchFactory $swatchFactory,
        \Magento\Swatches\Helper\Media $swatchHelper,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler
    ) {
        $this->swatchFactory = $swatchFactory;
        $this->swatchHelper = $swatchHelper;
        $this->configurableAttributeHandler = $configurableAttributeHandler;
        $this->collection = $configurableAttributeHandler->getApplicableAttributes();
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];

            foreach ($this->collection->getItems() as $attribute) {
                if ($this->configurableAttributeHandler->isAttributeApplicable($attribute)
                    && $attribute->getAttributeId() !== $this->skipAttributeId
                ) {
                    $value = [
                        'label' => $attribute->getFrontendLabel()
                    ];
                    $options = [];

                    foreach ($attribute->getOptions() as $option) {
                        $options[] = [
                            'value' => $option->getValue(),
                            'label' => $option->getLabel()
                        ];

                    }
                    $value['value'] = $options;
                    $this->options[] = $value;
                }
            }
        }

        return $this->options;
    }

    /**
     * @return array
     */
    public function toExtendedArray()
    {
        $data = [];
        foreach ($this->collection->getItems() as $attribute) {
            if ($this->configurableAttributeHandler->isAttributeApplicable($attribute)
                && $attribute->getAttributeId() !== $this->skipAttributeId
            ) {
                $options = [];
                foreach ($attribute->getOptions() as $option) {
                    $scope = [
                        'value' => $option->getValue(),
                        'label' => $option->getLabel()
                    ];
                    // phpcs:ignore
                    $options[] = array_merge(
                        $scope,
                        $this->getSwatches($option->getValue())
                    );
                }

                $data[$attribute->getAttributeId()] = [
                    'options' => $options,
                    'type' => $attribute->getFrontendInput()
                ];
            }
        }

        return $data;
    }

    /**
     * @param $optionId
     * @return mixed
     */
    private function getSwatches($optionId)
    {
        $data = ['type' => 0, 'swatch' => '', 'id' => $optionId];
        $swatchesItems = $this->getSwatchesItems();
        if (is_array($swatchesItems) && array_key_exists($optionId, $swatchesItems)) {
            $item = $swatchesItems[$optionId];
            $data['type'] = $item->getType();
            $data['swatch'] = ($item->getType() == 2) ?
                $this->swatchHelper->getSwatchMediaUrl() . $item->getValue():
                $item->getValue();
        }

        return $data;
    }

    private function getSwatchesItems()
    {
        if (!$this->swatchesOptions) {
            $collection = $this->swatchFactory->create()->getCollection()
                ->addFieldToFilter('store_id', 0);
            foreach ($collection as $item) {
                $this->swatchesOptions[$item->getOptionId()] = $item;
            }
        }

        return $this->swatchesOptions;
    }

    /**
     * @param $skipAttributeId
     * @return $this
     */
    public function skipAttributeId($skipAttributeId)
    {
        $this->skipAttributeId = $skipAttributeId;
        return $this;
    }
}
