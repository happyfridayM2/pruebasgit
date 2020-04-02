<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Adminhtml\Image;

use Amasty\PageSpeedOptimizer\Api\ImageSettingRepositoryInterface;
use Amasty\PageSpeedOptimizer\Controller\Adminhtml\AbstractImageSettings;
use Amasty\PageSpeedOptimizer\Controller\Adminhtml\RegistryConstants;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Edit extends AbstractImageSettings
{
    /**
     * @var ImageSettingRepositoryInterface
     */
    private $repository;

    public function __construct(
        ImageSettingRepositoryInterface $repository,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_PageSpeedOptimizer::image_settings');

        if ($imageSettingId = (int) $this->getRequest()->getParam(RegistryConstants::IMAGE_SETTING_ID)) {
            try {
                $this->repository->getById($imageSettingId);
                $resultPage->getConfig()->getTitle()->prepend(__('Edit Image Folder Settings'));
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This image settings no longer exists.'));

                return $this->_redirect('*/*/index');
            }
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Pattern For Image Folder Optimization'));
        }

        return $resultPage;
    }
}
