<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

interface OutputChainInterface
{
    /**
     * @param string $output
     *
     * @return string
     */
    public function process(&$output);
}
