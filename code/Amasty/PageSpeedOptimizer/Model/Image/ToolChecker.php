<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Image;

use Amasty\PageSpeedOptimizer\Exceptions\DisabledExecFunction;
use Amasty\PageSpeedOptimizer\Exceptions\ToolNotInstalled;
use Magento\Framework\Exception\LocalizedException;

class ToolChecker
{
    /**
     * @var \Magento\Framework\Shell
     */
    private $shell;

    public function __construct(
        \Magento\Framework\Shell $shell
    ) {
        $this->shell = $shell;
    }

    public function check($command)
    {
        if (empty($command['check']) || empty($command['check']['command']) || empty($command['check']['result'])) {
            return;
        }

        try {
            $output = $this->shell->execute($command['check']['command'] . ' 2>&1');
        } catch (LocalizedException $e) {
            throw new DisabledExecFunction();
        }

        if (!empty($output) && false !== strpos($output, $command['check']['result'])) {
            return;
        }

        throw new ToolNotInstalled(__('Image Optimization Tool "%1" is not installed', $command['name']));
    }
}
