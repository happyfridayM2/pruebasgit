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
use Amasty\PageSpeedOptimizer\Exceptions\DisabledExecFunction;
use Amasty\PageSpeedOptimizer\Exceptions\ToolNotInstalled;
use Amasty\PageSpeedOptimizer\Model\Image\ToolChecker;
use Amasty\PageSpeedOptimizer\Model\OptionSource\GifOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\JpegOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\PngOptimization;
use Amasty\PageSpeedOptimizer\Model\OptionSource\WebpOptimization;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends AbstractImageSettings
{
    /**
     * @var ImageSettingRepositoryInterface
     */
    private $repository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var bool
     */
    private $execChecked = false;

    /**
     * @var ToolChecker
     */
    private $toolChecker;

    public function __construct(
        Context $context,
        ImageSettingRepositoryInterface $repository,
        DataPersistorInterface $dataPersistor,
        ToolChecker $toolChecker
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->dataPersistor = $dataPersistor;
        $this->toolChecker = $toolChecker;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            try {
                $imageSettingId = 0;
                if ($imageSettingId = (int)$this->getRequest()->getParam(RegistryConstants::IMAGE_SETTING_ID)) {
                    $model = $this->repository->getById($imageSettingId);
                } else {
                    $model = $this->repository->getEmptyImageSettingModel();
                }

                $model->addData($data);
                $this->checkTools($model);
                $model->setFolders($model->getFolders());
                $this->repository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the item.'));

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/edit', [RegistryConstants::IMAGE_SETTING_ID => $model->getId()]);
                }

                if ($this->getRequest()->getParam('save_and_optimize')) {
                    $this->dataPersistor->set(RegistryConstants::OPTIMIZE, true);
                    return $this->_redirect(
                        '*/*/edit',
                        [RegistryConstants::IMAGE_SETTING_ID => $model->getId()]
                    );
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->dataPersistor->set(RegistryConstants::IMAGE_SETTING_DATA, $data);
                if ($imageSettingId) {
                    return $this->_redirect('*/*/edit', [RegistryConstants::IMAGE_SETTING_ID => $imageSettingId]);
                } else {
                    return $this->_redirect('*/*/create');
                }
            }
        }
        return $this->_redirect('*/*/');
    }

    /**
     * @param \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface $model
     */
    public function checkTools($model)
    {
        try {
            if ($model->getJpegTool()) {
                try {
                    $this->toolChecker->check(JpegOptimization::TOOLS[$model->getJpegTool()]);
                } catch (ToolNotInstalled $e) {
                    $this->messageManager->addWarningMessage($e->getMessage());
                }
            }

            if ($model->getPngTool()) {
                try {
                    $this->toolChecker->check(PngOptimization::TOOLS[$model->getPngTool()]);
                } catch (ToolNotInstalled $e) {
                    $this->messageManager->addWarningMessage($e->getMessage());
                }
            }

            if ($model->getGifTool()) {
                try {
                    $this->toolChecker->check(GifOptimization::TOOLS[$model->getGifTool()]);
                } catch (ToolNotInstalled $e) {
                    $this->messageManager->addWarningMessage($e->getMessage());
                }
            }

            if ($model->isCreateWebp()) {
                try {
                    $this->toolChecker->check(WebpOptimization::WEBP);
                } catch (ToolNotInstalled $e) {
                    $this->messageManager->addWarningMessage($e->getMessage());
                }
            }
        } catch (DisabledExecFunction $e) {
            $this->messageManager->addWarningMessage($e->getMessage());
        }
    }
}
