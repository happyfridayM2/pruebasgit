<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Image\DataProvider;

use Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface;
use Amasty\PageSpeedOptimizer\Api\ImageSettingRepositoryInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Listing extends AbstractDataProvider
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Image\ImageSetting
     */
    private $imageSettingModel;

    public function __construct(
        ImageSettingRepositoryInterface $repository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $repository->getImageSettingCollection();
        $this->imageSettingModel = $repository->getEmptyImageSettingModel();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        $data = parent::getData();
        if (!empty($data['totalRecords'])) {
            foreach ($data['items'] as &$item) {
                $item[ImageSettingInterface::FOLDERS] = $this->imageSettingModel
                    ->setData(ImageSettingInterface::FOLDERS, $item[ImageSettingInterface::FOLDERS])
                    ->getFolders();
            }
        }

        return $data;
    }
}
