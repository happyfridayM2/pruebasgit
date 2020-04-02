<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

class CheckIsOutputHtmlProcessor implements OutputProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function process(&$output)
    {
        if (!preg_match('/(<html[^>]*>)(?>.*?<body[^>]*>)(?>.*?<\/body>).*?<\/html>/Uis', $output)) {
            return false;
        }

        return true;
    }
}
