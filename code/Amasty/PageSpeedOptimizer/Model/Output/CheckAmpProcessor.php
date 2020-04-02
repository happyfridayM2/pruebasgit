<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

class CheckAmpProcessor implements OutputProcessorInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function process(&$output)
    {
        if (strpos($this->request->getOriginalPathInfo(), '/amp/') !== false
            || $this->request->getParam('amp')
        ) {
            return false;
        }

        return true;
    }
}
