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
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Delete extends AbstractImageSettings
{
    /**
     * @var ImageSettingRepositoryInterface
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        ImageSettingRepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * Mass action execution
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!($imageSettingId = (int)$this->getRequest()->getParam(RegistryConstants::IMAGE_SETTING_ID))) {

            return $this->_redirect('*/*/index');
        }
        try {
            $this->repository->deleteById($imageSettingId);
            $this->messageManager->addSuccessMessage(__('Image setting has been successfully deleted'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $this->_redirect('*/*/index');
        } catch (\Exception $e) {
            $this->logger->error(
                __('Error occurred while deleting image setting with ID %1. Error: %2'),
                [$imageSettingId, $e->getMessage()]
            );
        }

        return $this->_redirect('*/*/index');
    }
}
