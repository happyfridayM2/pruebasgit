<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

class MoveJsProcessor implements OutputProcessorInterface
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Code\Minifier\Adapter\Js\JShrink
     */
    private $JShrink;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Js\ScriptsExtractor
     */
    private $scriptsExtractor;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider,
        \Magento\Framework\App\RequestInterface $request,
        \Amasty\PageSpeedOptimizer\Model\Js\ScriptsExtractor $scriptsExtractor,
        \Magento\Framework\Code\Minifier\Adapter\Js\JShrink $JShrink
    ) {
        $this->configProvider = $configProvider;
        $this->JShrink = $JShrink;
        $this->scriptsExtractor = $scriptsExtractor;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function process(&$output)
    {
        if ($this->configProvider->isMoveJS() && $this->scriptsExtractor->canProcessPage()
            && !$this->request->getParam('amoptimizer_not_move')
        ) {
            list($output, $scripts) = $this->scriptsExtractor->extract($output, true);

            $scriptsOutput = '';
            foreach ($scripts as $script) {
                try {
                    $scriptMin = $this->JShrink->minify($script);
                    if (strpos($scriptMin, '<script') === false
                        || strpos($scriptMin, '</script') === false
                    ) {
                        $scriptsOutput .= $script;
                    } else {
                        $scriptsOutput .= $scriptMin;
                    }
                } catch (\Exception $e) {
                    $scriptsOutput .= $script;
                }
            }

            $output = str_ireplace('</body', $scriptsOutput . '</body', $output);
        }

        return true;
    }
}
