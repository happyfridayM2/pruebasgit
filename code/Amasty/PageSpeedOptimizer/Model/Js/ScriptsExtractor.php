<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Js;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Magento\Framework\UrlInterface;

class ScriptsExtractor
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        ConfigProvider $configProvider,
        UrlInterface $url
    ) {
        $this->configProvider = $configProvider;
        $this->url = $url;
    }

    /**
     * @param string $input
     * @param bool $scriptsAsArray
     *
     * @return array
     */
    public function extract($input, $scriptsAsArray = false)
    {
        if ($scriptsAsArray) {
            $scripts = [];
        } else {
            $scripts = '';
        }

        $ignoreParts = $this->configProvider->getMoveJsExcludePart();
        $input = preg_replace_callback(
            '/<script[^>]*>(?>.*?<\/script>)/is',
            function ($script) use (&$scripts, $ignoreParts, $scriptsAsArray) {
                $ignore = false;
                if (!empty($ignoreParts)) {
                    foreach ($ignoreParts as $ignorePart) {
                        if (strpos($script[0], $ignorePart) !== false) {
                            $ignore = true;
                        }
                    }
                }

                if (!$ignore) {
                    if ($scriptsAsArray) {
                        $scripts[] = $script[0];
                    } else {
                        $scripts .= $script[0];
                    }

                    return '';
                } else {
                    return $script[0];
                }
            },
            $input
        );

        return [$input, $scripts];
    }

    /**
     * @return bool
     */
    public function canProcessPage()
    {
        $process = true;
        if ($exclude = $this->configProvider->getMoveJsExcludeUrl()) {
            $url = $this->url->getCurrentUrl();
            foreach ($exclude as $urlPart) {
                if (strpos($url, $urlPart) !== false) {
                    $process = false;
                }
            }
        }

        return $process;
    }
}
