<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

use Amasty\PageSpeedOptimizer\Model\HeaderProvider\IsSetXFrameOptions;

class CheckBundlingProcessor implements OutputProcessorInterface
{
    /**
     * Wait N sec until process loaded modules
     */
    const WAIT_TIME = 4;
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var IsSetXFrameOptions
     */
    private $isSetXFrameOptions;

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider,
        \Magento\Framework\App\RequestInterface $request,
        \Amasty\PageSpeedOptimizer\Model\HeaderProvider\IsSetXFrameOptions $isSetXFrameOptions,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->isSetXFrameOptions = $isSetXFrameOptions;
    }

    /**
     * @inheritdoc
     */
    public function process(&$output)
    {
        if ($hash = $this->request->getParam('amoptimizer_bundle_check')) {
            if ($hash === $this->configProvider->getBundleHash()) {
                $baseUrl = $this->request->getParam('bu');
                $this->isSetXFrameOptions->setIsSetHeader(true)->setBaseUrl($baseUrl);
                //cross domain request fix
                $bundleUrl = $this->storeManager->getStore()->getBaseUrl() . 'amoptimizer/bundle/modules'
                    . '?___store=' . $this->storeManager->getStore()->getCode();

                $bundleScript = '
                <script>
                    if (typeof require.config === "undefined" && window.parent !== window) {
                        window.parent.postMessage(\'Error!\', \'' . $baseUrl . '\');
                    }
                    require(["jquery", "underscore", "domReady!"], function($, _) {
                        var mc = 0,
                            t = null;
                        setTimeout(function() {
                            mc = _.size(require.s.contexts._.urlFetched);
                        }, ' . ((self::WAIT_TIME - 1) * 1000) . ');
                        t = setInterval(function() {
                            window.parent.postMessage(\'Working!\', \'' . $baseUrl . '\');
                            if (mc === _.size(require.s.contexts._.urlFetched)) {
                                clearInterval(t);
                                var dat = _.keys(require.s.contexts._.urlFetched);
                                _.each(require.s.contexts._.defined, function (val, key) {
                                    if (key.substr(0, 5) === "text!") {
                                        dat.push(require.toUrl(key.substr(5)));
                                    }
                                });
                                $.post("' . $bundleUrl . '", {data: JSON.stringify(dat)}, function () {
                                    if (window.parent === window) {
                                        alert("' . __('You can close the window') . '");
                                    } else {
                                        window.parent.postMessage(\'Done!\', \'' . $baseUrl . '\');
                                    }
                                });
                            } else {
                                mc = _.size(require.s.contexts._.urlFetched);
                            }
                        }, ' . (self::WAIT_TIME * 1000) . ');
                    });
                </script>';

                $output = str_ireplace('</body', $bundleScript . '</body', $output);
            }
        }

        return true;
    }
}
