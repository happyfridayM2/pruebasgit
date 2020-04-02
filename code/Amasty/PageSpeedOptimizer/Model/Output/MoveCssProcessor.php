<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

class MoveCssProcessor implements OutputProcessorInterface
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(\Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @inheritdoc
     */
    public function process(&$output)
    {
        $moveStyles = '';
        if ($this->configProvider->isMovePrintCss()) {
            $output = preg_replace_callback(
                '/\<link[^>]*media\s*=\s*["\']+print["\']+[^>]*\>/si',
                function ($print) use (&$moveStyles) {
                    $moveStyles .= $print[0];
                    return '';
                },
                $output
            );
        }

        if ($this->configProvider->isMoveFont()
            && preg_match('/<link[^>]*href\s*=\s*["\']+([^"\']*merged[^"\']*)["\']+[^>]*\>/is', $output, $m)
        ) {
            $fontLink = str_replace(
                $this->basename($m[1]),
                'fonts_' . $this->basename($m[1]),
                $m[1]
            );
            $moveStyles .= '<link rel="stylesheet"  type="text/css"  media="all" href="' . $fontLink . '" />';
        }

        if (!empty($moveStyles)) {
            $moveStyles = '<noscript id="deferred-css">' . $moveStyles . '</noscript><script>'
                . 'var loadDeferredStyles = function() {'
                . 'var addStylesNode = document.getElementById("deferred-css");'
                . 'var replacement = document.createElement("div");'
                . 'replacement.innerHTML = addStylesNode.textContent;'
                . 'document.body.appendChild(replacement);'
                . 'addStylesNode.parentElement.removeChild(addStylesNode);'
                . '};'
                . 'window.addEventListener(\'load\', loadDeferredStyles);</script>';

            $output = str_ireplace('</body', $moveStyles . '</body', $output);
        }

        return true;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function basename($file)
    {
        //phpcs:ignore
        return basename($file);
    }
}
