<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\GoogleWizard;

use Amasty\Feed\Controller\Adminhtml\AbstractGoogleWizard;
use Amasty\Feed\Model\RegistryContainer;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 *
 * @package Amasty\Feed
 */
class Index extends AbstractGoogleWizard
{
    /**
     * @var RegistryContainer
     */
    private $registryContainer;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Feed\Model\RegistryContainer $registryContainer
    ) {
        parent::__construct($context);
        $this->registryContainer = $registryContainer;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $valueOfFirstStep = RegistryContainer::VALUE_FIRST_STEP;
        $step = $this->getRequest()->getParam(RegistryContainer::VAR_STEP, $valueOfFirstStep);
        $categoryMappedId = $this->getRequest()->getParam(RegistryContainer::VAR_CATEGORY_MAPPER);
        $identifierExistsId = $this->getRequest()->getParam(RegistryContainer::VAR_IDENTIFIER_EXISTS);
        $feedId = $this->getRequest()->getParam(RegistryContainer::VAR_FEED);

        $this->registryContainer->setValue(RegistryContainer::VAR_CATEGORY_MAPPER, $categoryMappedId);
        $this->registryContainer->setValue(RegistryContainer::VAR_FEED, $feedId);
        $this->registryContainer->setValue(RegistryContainer::VAR_IDENTIFIER_EXISTS, $identifierExistsId);
        $this->registryContainer->setValue(RegistryContainer::VAR_STEP, $step);

        $resultPage->setActiveMenu('Amasty_Feed::feed');
        $resultPage->addBreadcrumb(__('Amasty Feed'), __('Amasty Feed'));
        $resultPage->addBreadcrumb(__('Google Feed Wizard'), __('Google Feed Wizard'));
        $resultPage->getConfig()->getTitle()->prepend(__('Google Feed Wizard'));

        return $resultPage;
    }
}
