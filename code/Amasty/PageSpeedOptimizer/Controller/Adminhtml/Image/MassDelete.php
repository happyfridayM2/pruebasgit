<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Adminhtml\Image;

use Amasty\PageSpeedOptimizer\Api\ImageSettingRepositoryInterface;
use Amasty\PageSpeedOptimizer\Controller\Adminhtml\AbstractImageSettings;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractImageSettings
{
    /**
     * @var ImageSettingRepositoryInterface
     */
    private $repository;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        ImageSettingRepositoryInterface $repository,
        Filter $filter,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->filter = $filter;
        $this->logger = $logger;
    }

    /**
     * Mass action execution
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider();

        $collection = $this->filter->getCollection($this->repository->getImageSettingCollection());
        $deleted = 0;
        $failed = 0;

        if ($collection->count()) {
            foreach ($collection->getItems() as $imageSetting) {
                try {
                    $this->repository->delete($imageSetting);
                    $deleted++;
                } catch (LocalizedException $e) {
                    $failed++;
                } catch (\Exception $e) {
                    $this->logger->error(
                        __('Error occurred while deleting image setting with ID %1. Error: %2'),
                        [$imageSetting->getImageSettingId(), $e->getMessage()]
                    );
                }
            }
        }

        if ($deleted !== 0) {
            $this->messageManager->addSuccessMessage(
                __('%1 image setting(s) has been successfully deleted', $deleted)
            );
        }

        if ($failed !== 0) {
            $this->messageManager->addErrorMessage(
                __('%1 image setting(s) has been failed to delete', $failed)
            );
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
