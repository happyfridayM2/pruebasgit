<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Observer;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Amasty\PageSpeedOptimizer\Model\Output\LazyLoadProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ObserverInterface;

class AjaxLazyLoadProcessor implements ObserverInterface
{
    /**
     * @var LazyLoadProcessor
     */
    private $lazyLoadProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        LazyLoadProcessor $lazyLoadProcessor
    ) {
        $this->lazyLoadProcessor = $lazyLoadProcessor;
        $this->configProvider = $configProvider;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }

        /** @var DataObject $data */
        if ($data = $observer->getData('data')) {
            if ($data->hasData('pageType')) {
                $this->lazyLoadProcessor->setPageType($data->getData('pageType'));
            }
            $lazyConfig = $this->lazyLoadProcessor->getLazyConfig();
            if ($data->hasData('lazyConfig')) {
                $newLazyConfig = array_merge_recursive($lazyConfig->getData(), $data->getData('lazyConfig'));
                $lazyConfig->unsetData()->setData($newLazyConfig);
                $this->lazyLoadProcessor->setLazyConfig($lazyConfig);
            }
            $page = $data->getData('page');
            $this->lazyLoadProcessor->processImages($page);
            if ($lazyConfig->getData(LazyLoadProcessor::IS_LAZY)) {
                $page .= '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4n'
                    . 'GP6zwAAAgcBApocMXEAAAAASUVORK5CYII=" onload="amlazy();this.remove();"/>';
            }
            $data->setData('page', $page);
        }
    }
}
