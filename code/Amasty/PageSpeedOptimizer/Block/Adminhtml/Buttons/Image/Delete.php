<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Block\Adminhtml\Buttons\Image;

use Amasty\PageSpeedOptimizer\Block\Adminhtml\Buttons\GenericButton;
use Amasty\PageSpeedOptimizer\Controller\Adminhtml\RegistryConstants;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Delete extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->getImageSettingId()) {
            return [];
        }
        $alertMessage = __('Are you sure you want to do this?');
        $onClick = sprintf('deleteConfirm("%s", "%s")', $alertMessage, $this->getDeleteUrl());

        $data = [
            'label' => __('Delete'),
            'class' => 'delete',
            'id' => 'image-setting-edit-delete-button',
            'on_click' => $onClick,
            'sort_order' => 20,
        ];

        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', [RegistryConstants::IMAGE_SETTING_ID => $this->getImageSettingId()]);
    }

    /**
     * @return null|int
     */
    public function getImageSettingId()
    {
        return (int)$this->request->getParam(RegistryConstants::IMAGE_SETTING_ID);
    }
}
