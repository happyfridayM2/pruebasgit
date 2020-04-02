<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Adminhtml\Bundle;

use Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\Bundle;
use Magento\Backend\App\Action;

class Clear extends Action
{
    /**
     * @var Bundle
     */
    private $bundleResource;

    public function __construct(Bundle $bundleResource, Action\Context $context)
    {
        parent::__construct($context);
        $this->bundleResource = $bundleResource;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $this->bundleResource->clear();

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
