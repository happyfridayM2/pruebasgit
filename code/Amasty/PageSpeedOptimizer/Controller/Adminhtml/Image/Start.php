<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Adminhtml\Image;

use Amasty\PageSpeedOptimizer\Controller\Adminhtml\AbstractImageSettings;
use Amasty\PageSpeedOptimizer\Controller\Adminhtml\RegistryConstants;
use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Amasty\PageSpeedOptimizer\Model\Image\GenerateQueue;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Start extends AbstractImageSettings
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GenerateQueue
     */
    private $generateQueue;

    public function __construct(
        GenerateQueue $generateQueue,
        ConfigProvider $configProvider,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->configProvider = $configProvider;
        $this->generateQueue = $generateQueue;
    }

    public function execute()
    {
        $queueSize = $this->generateQueue->generateQueue(
            $this->getRequest()->getParam(RegistryConstants::IMAGE_SETTING_ID)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
            'filesCount' => $queueSize,
            'filesPerRequest' => $this->configProvider->getImagesPerRequest()
        ]);
    }
}
