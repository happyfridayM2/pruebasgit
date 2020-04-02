<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Adminhtml\Image;

use Amasty\PageSpeedOptimizer\Controller\Adminhtml\AbstractImageSettings;
use Amasty\PageSpeedOptimizer\Model\Image\ForceOptimization;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

class Process extends AbstractImageSettings
{
    /**
     * @var ForceOptimization
     */
    private $forceOptimization;

    public function __construct(
        ForceOptimization $forceOptimization,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->forceOptimization = $forceOptimization;
    }

    public function execute()
    {
        $limit = (int)$this->getRequest()->getParam('limit', 10);
        if (!$limit || $limit < 0) {
            $limit = 10;
        }
        $this->forceOptimization->execute($limit);

        return $this->resultFactory->create(ResultFactory::TYPE_RAW)->setContents(1);
    }
}
