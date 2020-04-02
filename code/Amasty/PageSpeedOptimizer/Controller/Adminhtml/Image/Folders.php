<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Adminhtml\Image;

use Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface;
use Amasty\PageSpeedOptimizer\Controller\Adminhtml\AbstractImageSettings;
use Amasty\PageSpeedOptimizer\Controller\Adminhtml\RegistryConstants;
use Amasty\PageSpeedOptimizer\Model\Image\ResourceModel\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;

class Folders extends AbstractImageSettings
{
    const FOLDER_MAX_DEPTH_LEVEL = 3;

    /**
     * @var Filesystem\Directory\ReadInterface
     */
    private $mediaDirectory;

    /**
     * @var array
     */
    private $excludeFolders;

    /**
     * @var CollectionFactory
     */
    private $imageCollectionFactory;

    public function __construct(
        Action\Context $context,
        CollectionFactory $imageCollectionFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->imageCollectionFactory = $imageCollectionFactory;
    }

    /**
     * Mass action execution
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $imageSettingCollection = $this->imageCollectionFactory->create();
        if ($imageSettingId = $this->getRequest()->getParam(RegistryConstants::IMAGE_SETTING_ID)) {
            $imageSettingCollection->addFieldToFilter(
                ImageSettingInterface::IMAGE_SETTING_ID,
                ['neq' => (int)$imageSettingId]
            );
        }
        $this->excludeFolders = [];
        /** @var \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface $item */
        foreach ($imageSettingCollection->getItems() as $item) {
            //phpcs:ignore
            $this->excludeFolders = array_merge($this->excludeFolders, $item->getFolders());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($this->getFolders('.'));
    }

    public function getFolders($path, $level = 0)
    {
        $result = [];
        $folders = $this->mediaDirectory->read($path);
        foreach ($folders as $folder) {
            if ($this->mediaDirectory->isDirectory($folder)) {
                $folder = preg_replace('/^\.\/(.*)/is', '$1', $folder);
                if ($level < self::FOLDER_MAX_DEPTH_LEVEL) {
                    $result[] = [
                        'label' => $folder,
                        'value' => $folder,
                        'level' => $level,
                        'optgroup' => $this->getFolders($folder, $level + 1),
                        'disabled' => in_array($folder, $this->excludeFolders)
                    ];
                    if (empty($result[count($result) - 1]['optgroup'])) {
                        unset($result[count($result) - 1]['optgroup']);
                    }
                }
            }
        }

        return $result;
    }
}
