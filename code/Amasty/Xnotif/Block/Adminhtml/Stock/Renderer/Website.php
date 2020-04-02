<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */
namespace Amasty\Xnotif\Block\Adminhtml\Stock\Renderer;

use \Magento\Framework\DataObject;

/**
 * Class Website
 */
class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $value = $row->getWebsiteId();
        $value = $this->convertIdsToLabels($value);

        return $value;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function convertIdsToLabels($value)
    {
        $value = explode(',', $value);
        $websites = $this->getWebsiteOptions();

        $webSitesLabels = [];
        foreach ($websites as $website) {
            if (array_search($website['value'], $value) !== false) {
                $webSitesLabels[] = $website['label'];
            }
        }

        return implode(", ", array_unique($webSitesLabels));
    }

    /**
     * @return array
     */
    protected function getWebsiteOptions()
    {
        return $this->collectionFactory->create()->toOptionArray();
    }
}
