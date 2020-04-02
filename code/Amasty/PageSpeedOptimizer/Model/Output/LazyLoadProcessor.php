<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Output;

use Amasty\PageSpeedOptimizer\Model\ConfigProvider;
use Amasty\PageSpeedOptimizer\Model\Image\OutputImage;
use Amasty\PageSpeedOptimizer\Model\OptionSource\LazyLoadScript;
use Amasty\PageSpeedOptimizer\Model\OptionSource\PreloadStrategy;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Layout;

class LazyLoadProcessor implements OutputProcessorInterface
{
    const IMAGE_REGEXP = '<img([^>]*?)src=(\"|\'|)(.*?)(\"|\'| )(.*?)>';
    const LAZY_LOAD_PLACEHOLDER = 'src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABC'
        . 'AQAAAC1HAwCAAAAC0lEQVR4nGP6zwAAAgcBApocMXEAAAAASUVORK5CYII="';

    const HOME = 'cms_index_index';
    const CATEGORY = 'catalog_category_view';
    const PRODUCT = 'catalog_product_view';
    const CMS = 'cms_page_view';
    const GENERAL = 'general';

    const PAGE_CONFIG = [
        self::HOME => 'lazy_load_home',
        self::CATEGORY => 'lazy_load_categories',
        self::PRODUCT => 'lazy_load_products',
        self::CMS => 'lazy_load_cms',
        self::GENERAL => 'lazy_load_general'
    ];

    const IS_SIMPLE = 'is_simple';
    const IS_REPLACE_WITH_USER_AGENT = 'is_replace_with_user_agent';
    const IS_LAZY = 'is_lazy';
    const LAZY_IGNORE_LIST = 'lazy_ignore_list';
    const LAZY_SKIP_IMAGES = 'lazy_skip_images';
    const LAZY_PRELOAD_STRATEGY = 'lazy_preload_strategy';
    const LAZY_SCRIPT = 'lazy_script';
    const REPLACE_IMAGES_IF_NOT_LAZY = 'replace_images_if_not_lazy';
    const REPLACE_IMAGES_IGNORE_LIST = 'replace_images_ignore_list';

    /**
     * @var string
     */
    public $pageType;

    /**
     * @var Layout
     */
    private $layout;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var OutputImage
     */
    private $outputImage;

    /**
     * @var string
     */
    private $deviceType;

    /**
     * @var false
     */
    private $isWebpSupport;

    /**
     * @var DataObjectFactory
     */
    private $lazyConfigFactory;

    /**
     * @var DataObject
     */
    private $lazyConfig;

    /**
     * @var DeviceDetect
     */
    private $deviceDetect;

    public function __construct(
        Layout $layout,
        ConfigProvider $configProvider,
        Repository $assetRepo,
        OutputImage $outputImage,
        DataObjectFactory $lazyConfigFactory,
        DeviceDetect $deviceDetect
    ) {
        $this->layout = $layout;
        $this->configProvider = $configProvider;
        $this->assetRepo = $assetRepo;
        $this->outputImage = $outputImage;
        $this->lazyConfigFactory = $lazyConfigFactory;
        $this->deviceDetect = $deviceDetect;
    }

    /**
     * @inheritdoc
     */
    public function process(&$output)
    {
        $this->detectPage($this->layout->getUpdate()->getHandles());

        $this->prepareLazyConfig();
        $this->processImages($output);

        if ($this->lazyConfig->getData(self::IS_LAZY) && $this->lazyConfig->hasData(self::LAZY_SCRIPT)) {
            $this->addLazyScript($output, $this->lazyConfig->getData(self::LAZY_SCRIPT));
        }

        return true;
    }

    public function processImages(&$output)
    {
        if ($this->lazyConfig->getData(self::IS_LAZY)) {
            $this->processLazyImages($output);
        } elseif ($this->lazyConfig->getData(self::REPLACE_IMAGES_IF_NOT_LAZY)
            || $this->lazyConfig->getData(self::IS_REPLACE_WITH_USER_AGENT)
        ) {
            $this->replaceImages($output);
        }
    }

    public function processLazyImages(&$output)
    {
        $tempOutput = preg_replace('/<script[^>]*>(?>.*?<\/script>)/is', '', $output);
        if (preg_match_all('/' . self::IMAGE_REGEXP . '/is', $tempOutput, $images)) {
            $skipCounter = 1;
            $preloadStrategy = $this->lazyConfig->getData(self::LAZY_PRELOAD_STRATEGY);
            foreach ($images[0] as $key => $image) {
                if ($this->skipIfContain($image, $this->lazyConfig->getData(self::LAZY_IGNORE_LIST))) {
                    continue;
                }

                if ($skipCounter < $this->lazyConfig->getData(self::LAZY_SKIP_IMAGES)) {
                    if ($this->lazyConfig->getData(self::IS_REPLACE_WITH_USER_AGENT)) {
                        $newImg = $this->replaceWithBest($image, $images[3][$key]);
                        $output = str_replace($image, $newImg, $output);
                    } else {
                        if ($preloadStrategy == PreloadStrategy::SKIP_IMAGES) {
                            $skipCounter++;
                            continue;
                        }
                        $newImg = $this->replaceWithPictureTag($image, $images[3][$key]);
                        $output = str_replace($image, $newImg, $output);
                    }
                    $skipCounter++;
                    continue;
                }

                $replace = 'src=' . $images[2][$key] . $images[3][$key] . $images[4][$key];

                $newImg = str_replace($replace, self::LAZY_LOAD_PLACEHOLDER . ' data-am' . $replace, $image);
                if ($this->lazyConfig->getData(self::IS_REPLACE_WITH_USER_AGENT)) {
                    $newImg = $this->replaceWithBest($newImg, $images[3][$key]);
                }
                $newImg = preg_replace('/srcset=[\"\'\s]+(.*?)[\"\']+/is', '', $newImg);
                $output = str_replace($image, $newImg, $output);
            }
        }
    }

    public function replaceImages(&$output)
    {
        $tempOutput = preg_replace('/<script.*?>.*?<\/script.*?>/is', '', $output);
        if (preg_match_all('/' . self::IMAGE_REGEXP . '/is', $tempOutput, $images)) {
            foreach ($images[0] as $key => $image) {
                if ($this->skipIfContain($image, $this->lazyConfig->getData(self::REPLACE_IMAGES_IGNORE_LIST))) {
                    continue;
                }

                if ($this->lazyConfig->getData(self::IS_REPLACE_WITH_USER_AGENT)) {
                    $newImg = $this->replaceWithBest($image, $images[3][$key]);
                } else {
                    $newImg = $this->replaceWithPictureTag($image, $images[3][$key]);
                }

                $output = str_replace($image, $newImg, $output);
            }
        }
    }

    public function replaceWithPictureTag($image, $imagePath)
    {
        $outputImage = $this->outputImage->setPath($imagePath);
        if ($outputImage->process() && $sourceSet = $outputImage->getSourceSet()) {
            return '<picture>' . $sourceSet . $image . '</picture>';
        }

        return $image;
    }

    public function replaceWithBest($image, $imagePath)
    {
        $outputImage = $this->outputImage->setPath($imagePath);
        if ($outputImage->process()) {
            return str_replace(
                $imagePath,
                $outputImage->getBest($this->getDeviceType(), $this->isSupportWebp()),
                $image
            );
        }

        return $image;
    }

    public function addLazyScript(&$output, $lazyScript)
    {
        $lazy = '<script>window.amlazy = function() {'
            . 'if (typeof window.amlazycallback !== "undefined") {'
            . 'setTimeout(window.amlazycallback, 500);setTimeout(window.amlazycallback, 1500);}'
            . '}</script>';
        switch ($lazyScript) {
            case LazyLoadScript::NATIVE_LAZY:
                $lazy .= '<script>' . \Amasty\PageSpeedOptimizer\Model\Js\NativeJsUglify::SCRIPT . '</script>';
                break;
            case LazyLoadScript::JQUERY_LAZY:
            default:
                $lazy .= '<script>
                        window.amlazycallback = function () {
                            window.jQuery("img[data-amsrc]").lazy({"bind":"event", "attribute": "data-amsrc"});
                        };
                        require(["jquery"], function (jquery) {
                            require(["Amasty_PageSpeedOptimizer/js/jquery.lazy"], function(lazy) {
                                if (document.readyState === "complete") {
                                    window.jQuery("img[data-amsrc]").lazy({"bind":"event", "attribute": "data-amsrc"});
                                } else {
                                    window.jQuery("img[data-amsrc]").lazy({"attribute": "data-amsrc"});
                                }
                            })
                        });
                    </script>';
                break;
        }
        $output = str_ireplace('</body', $lazy . '</body', $output);
    }

    /**
     * @param array $handles
     */
    public function detectPage($handles = [])
    {
        if (in_array(self::HOME, $handles)) {
            $this->setPageType(self::HOME);
        } elseif (in_array(self::CMS, $handles)) {
            $this->setPageType(self::CMS);
        } elseif (in_array(self::CATEGORY, $handles)) {
            $this->setPageType(self::CATEGORY);
        } elseif (in_array(self::PRODUCT, $handles)) {
            $this->setPageType(self::PRODUCT);
        } else {
            $this->setPageType(self::GENERAL);
        }
    }

    public function setPageType($pageType)
    {
        $this->pageType = $pageType;
    }

    public function getPageType()
    {
        if (!array_key_exists($this->pageType, self::PAGE_CONFIG)) {
            return self::GENERAL;
        }

        return $this->pageType;
    }

    public function getDeviceType()
    {
        if ($this->deviceType === null) {
            $this->deviceType = $this->deviceDetect->getDeviceType();
        }

        return $this->deviceType;
    }

    public function isSupportWebp()
    {
        if ($this->isWebpSupport === null) {
            $this->isWebpSupport = $this->deviceDetect->isUseWebP();
        }

        return $this->isWebpSupport;
    }

    private function skipIfContain(string $searchString, array $list)
    {
        $skip = false;
        foreach ($list as $item) {
            if (strpos($searchString, $item) !== false) {
                $skip = true;
                break;
            }
        }

        return $skip;
    }

    public function setLazyConfig(DataObject $lazyConfig)
    {
        $this->lazyConfig = $lazyConfig;

        return $this;
    }

    public function getLazyConfig()
    {
        if ($this->lazyConfig === null) {
            $this->prepareLazyConfig();
        }

        return $this->lazyConfig;
    }

    public function prepareLazyConfig()
    {
        $lazyConfig = $this->lazyConfigFactory->create();
        $lazyConfig->setData(self::IS_SIMPLE, $this->configProvider->isSimpleOptimization());
        $lazyConfig->setData(self::IS_REPLACE_WITH_USER_AGENT, $this->configProvider->isReplaceImagesUsingUserAgent());

        if ($this->configProvider->isSimpleOptimization()) {
            $isLazy = $this->configProvider->isLazyLoad();
        } else {
            $isLazy = $this->configProvider->getConfig(
                self::PAGE_CONFIG[$this->getPageType()] . ConfigProvider::PART_IS_LAZY
            );
        }
        $lazyConfig->setData(self::IS_LAZY, $isLazy);

        $userAgentIgnoreList = [];
        if ($this->configProvider->isReplaceImagesUsingUserAgent() && !empty($this->getDeviceType())) {
            $type = '_' . $this->getDeviceType();
            $userAgentIgnoreList = $this->configProvider->getReplaceImagesUsingUserAgentIgnoreList();
        } else {
            $type = '';
        }

        $skipImages = false;
        $skipStrategy = PreloadStrategy::SKIP_IMAGES;
        if (!$this->configProvider->isSimpleOptimization()) {
            $lazyScript = $this->configProvider->getConfig(
                self::PAGE_CONFIG[$this->getPageType()] . ConfigProvider::PART_SCRIPT
            );
            $ignoreList = $this->configProvider->convertStringToArray(
                $this->configProvider->getConfig(
                    self::PAGE_CONFIG[$this->getPageType()] . ConfigProvider::PART_IGNORE
                )
            );

            if ($this->configProvider->getConfig(
                self::PAGE_CONFIG[$this->getPageType()] . ConfigProvider::PART_PRELOAD
            )) {
                $skipImages = $this->configProvider->getConfig(
                    self::PAGE_CONFIG[$this->getPageType()] . ConfigProvider::PART_SKIP . $type
                );
                $skipStrategy = $this->configProvider->getConfig(
                    self::PAGE_CONFIG[$this->getPageType()] . ConfigProvider::PART_STRATEGY
                );
            }
        } else {
            $lazyScript = $this->configProvider->lazyLoadScript();
            $ignoreList = $this->configProvider->getIgnoreImages();
            if ($this->configProvider->isPreloadImages()) {
                $skipImages = $this->configProvider->skipImagesCount($type);
                $skipStrategy = $this->configProvider->getSkipStrategy();
            }
        }

        if ($skipImages === false) {
            $skipImages = 0;
        }
        $ignoreList = array_merge($ignoreList, $userAgentIgnoreList);
        $lazyConfig->setData(self::LAZY_IGNORE_LIST, $ignoreList);
        $lazyConfig->setData(self::LAZY_SKIP_IMAGES, $skipImages);
        $lazyConfig->setData(self::LAZY_PRELOAD_STRATEGY, $skipStrategy);

        $lazyConfig->setData(self::LAZY_SCRIPT, $lazyScript);

        if ($this->configProvider->isSimpleOptimization()) {
            $isReplaceImages = $this->configProvider->isReplaceWithWebp();
        } else {
            $isReplaceImages = (bool)$this->configProvider->getConfig(
                self::PAGE_CONFIG[$this->getPageType()] . ConfigProvider::PART_REPLACE_WITH_WEBP
            );
        }
        $lazyConfig->setData(self::REPLACE_IMAGES_IF_NOT_LAZY, $isReplaceImages);

        if ($this->configProvider->isReplaceImagesUsingUserAgent()) {
            $replaceImagesIgnoreList = $this->configProvider->getReplaceImagesUsingUserAgentIgnoreList();
        } else {
            if (!$this->configProvider->isSimpleOptimization()) {
                $replaceImagesIgnoreList = $this->configProvider->convertStringToArray(
                    $this->configProvider->getConfig(
                        self::PAGE_CONFIG[$this->getPageType()] . ConfigProvider::PART_REPLACE_IGNORE
                    )
                );
            } else {
                $replaceImagesIgnoreList = $this->configProvider->getReplaceIgnoreList();
            }
        }
        $lazyConfig->setData(self::REPLACE_IMAGES_IGNORE_LIST, $replaceImagesIgnoreList);

        $this->lazyConfig = $lazyConfig;
    }
}
