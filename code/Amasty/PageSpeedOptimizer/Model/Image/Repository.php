<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Image;

use Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface;
use Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterfaceFactory;
use Amasty\PageSpeedOptimizer\Api\ImageSettingRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements ImageSettingRepositoryInterface
{
    /**
     * @var ImageSettingInterfaceFactory
     */
    private $imageSettingFactory;

    /**
     * @var ResourceModel\ImageSetting
     */
    private $imageSettingResource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ImageSettingInterface[]
     */
    private $imageSettings;

    public function __construct(
        ImageSettingInterfaceFactory $imageSettingFactory,
        ResourceModel\ImageSetting $imageSettingResource,
        ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->imageSettingFactory = $imageSettingFactory;
        $this->imageSettingResource = $imageSettingResource;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getById($imageSettingId)
    {
        if (!isset($this->imageSettings[$imageSettingId])) {
            $imageSetting = $this->getEmptyImageSettingModel();
            $this->imageSettingResource->load($imageSetting, $imageSettingId);
            if (!$imageSetting->getImageSettingId()) {
                throw new NoSuchEntityException(
                    __('Image Settings with specified ID "%1" not found.', $imageSettingId)
                );
            }
            $this->imageSettings[$imageSettingId] = $imageSetting;
        }

        return $this->imageSettings[$imageSettingId];
    }

    /**
     * @inheritDoc
     */
    public function save(\Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface $imageSetting)
    {
        try {
            if ($imageSetting->getImageSettingId()) {
                $imageSetting = $this->getById($imageSetting->getImageSettingId())->addData($imageSetting->getData());
            }
            $this->imageSettingResource->save($imageSetting);
            unset($this->imageSettings[$imageSetting->getImageSettingId()]);
        } catch (\Exception $e) {
            if ($imageSetting->getImageSettingId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save image setting with ID %1. Error: %2',
                        [$imageSetting->getImageSettingId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new image settings. Error: %1', $e->getMessage()));
        }

        return $imageSetting;
    }

    /**
     * @inheritDoc
     */
    public function delete(\Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface $imageSetting)
    {
        try {
            $this->imageSettingResource->delete($imageSetting);
            unset($this->imageSettings[$imageSetting->getImageSettingId()]);
        } catch (\Exception $e) {
            if ($imageSetting->getImageSettingId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove image settings with ID %1. Error: %2',
                        [$imageSetting->getIconId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove image settings. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($imageSettingId)
    {
        $this->delete($this->getById($imageSettingId));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getEmptyImageSettingModel()
    {
        return $this->imageSettingFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function getImageSettingCollection()
    {
        return $this->collectionFactory->create();
    }
}
