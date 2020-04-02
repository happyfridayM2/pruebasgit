<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    protected $pathPrefix = 'amoptimizer/';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    const XPATH_ENABLED = 'general/enabled';
    const IS_CLOUD = 'javascript/is_cloud';
    const BUNDLING_FILES = 'javascript/bundling_files';
    const MOVE_JS = 'settings/javascript/movejs';
    const MOVE_JS_EXCLUDE_URL = 'javascript/movejs_exclude_url';
    const MOVE_JS_EXCLUDE_PART = 'javascript/movejs_exclude_part';
    const ADMINHTML_JS_MERGE_BUNDLE = 'javascript/merge_and_bundle_adminhtml';
    const EXCLUDE_URLS_FROM_MERGE_BUNDLE = 'javascript/not_merge_and_bundle_urls';
    const MERGE_CSS_ADMINHTML = 'css/merge_css_adminhtml';
    const EXCLUDE_URLS_FROM_MERGE_CSS = 'css/not_merge_css_urls';
    const MOVE_PRINT_CSS = 'css/move_print';
    const MOVE_FONT = 'settings/css/move_font';
    const FONT_IGNORE_LIST = 'settings/css/font_ignore_list';
    const LAZY_LOAD = 'images/lazy_load';
    const LAZY_LOAD_SCRIPT = 'images/lazy_load_script';
    const PRELOAD_IMAGES = 'images/preload_images';
    const SKIP_IMAGES_COUNT = 'images/skip_images_count';
    const OPTIMIZE_IMAGES = 'images/optimize_images';
    const MULTIPROCESS_ENABLED = 'images/multiprocess_enabled';
    const MAX_JOBS_COUNT = 'images/process_count';
    const OPTIMIZE_AUTOMATICALLY = 'images/optimize_automatically';
    const IMAGES_PER_REQUEST = 'images/process_images_per_request';
    const REPLACE_WITH_WEBP = 'images/webp_resolutions';
    const REPLACE_IGNORE_IMAGES = 'images/webp_resolutions_ignore';
    const JPEG_COMMAND = 'images/jpeg_tool';
    const PNG_COMMAND = 'images/png_tool';
    const GIF_COMMAND = 'images/gif_tool';
    const DUMP_ORIGINAL = 'images/dump_original';
    const IGNORE_IMAGES = 'images/ignore_list';
    const RESOLUTIONS = 'images/resolutions';
    const RESIZE_ALGORITHM = 'images/resize_algorithm';
    const WEBP = 'images/webp';
    const SKIP_STRATEGY = 'images/preload_images_strategy';
    const IMAGE_OPTIMIZATION_TYPE = 'images/image_optimization_type';
    const REPLACE_IMAGES_USING_USER_AGENT = 'images/replace_images_using_user_agent';
    const REPLACE_IMAGES_USING_USER_AGENT_IGNORE_LIST = 'images/replace_images_using_user_agent_ignore_list';
    /**#@-*/

    const PART_IS_LAZY = '/lazy_load';
    const PART_SCRIPT = '/lazy_load_script';
    const PART_STRATEGY = '/preload_images_strategy';
    const PART_PRELOAD = '/preload_images';
    const PART_SKIP = '/skip_images_count';
    const PART_IGNORE = '/ignore_list';
    const PART_REPLACE_WITH_WEBP = '/webp_resolutions';
    const PART_REPLACE_IGNORE = '/webp_resolutions_ignore';
    const BUNDLING_TYPE = 'javascript/bundling_type';
    const BUNDLE_STEP = 'javascript/bundle_step';
    const BUNDLE_HASH = 'javascript/bundle_hash';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSetFlag(self::XPATH_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCloud()
    {
        return $this->isSetFlag(self::IS_CLOUD);
    }

    public function getBundlingFiles()
    {
        $bundlingFiles = $this->getValue(self::BUNDLING_FILES);
        if (!empty($bundlingFiles)) {
            $data = json_decode($bundlingFiles, true);
            if (json_last_error()) {
                return [];
            }

            return $data;
        }
        return [];
    }

    /**
     * @return bool
     */
    public function isMoveJS()
    {
        return $this->isSetFlag(self::MOVE_JS);
    }

    /**
     * @return bool
     */
    public function adminhtmlJsMergeBundle()
    {
        return $this->isSetFlag(self::ADMINHTML_JS_MERGE_BUNDLE);
    }

    /**
     * @return array
     */
    public function getExcludeUrlsFromMergeBundleJs()
    {
        return $this->convertStringToArray($this->getValue(self::EXCLUDE_URLS_FROM_MERGE_BUNDLE));
    }

    /**
     * @return bool
     */
    public function adminhtmlMergeCss()
    {
        return $this->isSetFlag(self::MERGE_CSS_ADMINHTML);
    }

    public function isMultiprocessEnabled()
    {
        return $this->isSetFlag(self::MULTIPROCESS_ENABLED);
    }

    public function getMaxJobsCount()
    {
        if (!function_exists('pcntl_fork')) {
            return 1;
        }
        $maxJobs = (int)$this->getValue(self::MAX_JOBS_COUNT) > 1
            ? (int)$this->getValue(self::MAX_JOBS_COUNT)
            : 1;

        return $this->isMultiprocessEnabled() ? $maxJobs : 1;
    }

    /**
     * @return array
     */
    public function getExcludeUrlsFromMergeCss()
    {
        return $this->convertStringToArray($this->getValue(self::EXCLUDE_URLS_FROM_MERGE_CSS));
    }

    /**
     * @return array
     */
    public function getMoveJsExcludeUrl()
    {
        return $this->convertStringToArray($this->getValue(self::MOVE_JS_EXCLUDE_URL));
    }

    /**
     * @return array
     */
    public function getMoveJsExcludePart()
    {
        return $this->convertStringToArray($this->getValue(self::MOVE_JS_EXCLUDE_PART));
    }

    /**
     * @return bool
     */
    public function isMovePrintCss()
    {
        return $this->isSetFlag(self::MOVE_PRINT_CSS);
    }

    /**
     * @return bool
     */
    public function isLazyLoad()
    {
        return $this->isSetFlag(self::LAZY_LOAD);
    }

    /**
     * @return int
     */
    public function lazyLoadScript()
    {
        return (int)$this->getValue(self::LAZY_LOAD_SCRIPT);
    }

    /**
     * @return bool
     */
    public function isOptimizeImages()
    {
        return (bool)$this->getValue(self::OPTIMIZE_IMAGES);
    }

    /**
     * @return bool
     */
    public function isAutomaticallyOptimizeImages()
    {
        return (bool)$this->getValue(self::OPTIMIZE_AUTOMATICALLY);
    }

    /**
     * @return int
     */
    public function getJpegCommand()
    {
        return (int)$this->getValue(self::JPEG_COMMAND);
    }

    /**
     * @return int
     */
    public function getPngCommand()
    {
        return (int)$this->getValue(self::PNG_COMMAND);
    }

    /**
     * @return int
     */
    public function getGifCommand()
    {
        return (int)$this->getValue(self::GIF_COMMAND);
    }

    /**
     * @return int
     */
    public function getImagesPerRequest()
    {
        return (int)$this->getValue(self::IMAGES_PER_REQUEST);
    }

    /**
     * @return bool
     */
    public function isMoveFont()
    {
        return $this->isSetFlag(self::MOVE_FONT);
    }

    /**
     * @return array
     */
    public function getFontIgnoreList()
    {
        return $this->convertStringToArray($this->getValue(self::FONT_IGNORE_LIST));
    }

    /**
     * @return bool
     */
    public function isCreateWebp()
    {
        return $this->isSetFlag(self::WEBP);
    }

    /**
     * @return bool
     */
    public function isDumpOriginal()
    {
        return $this->isSetFlag(self::DUMP_ORIGINAL);
    }

    /**
     * @return bool
     */
    public function isPreloadImages()
    {
        return $this->isSetFlag(self::PRELOAD_IMAGES);
    }

    /**
     * @param string $type
     *
     * @return int
     */
    public function skipImagesCount($type = '')
    {
        return (int)$this->getValue(self::SKIP_IMAGES_COUNT . $type);
    }

    /**
     * @return array
     */
    public function getIgnoreImages()
    {
        return $this->convertStringToArray($this->getValue(self::IGNORE_IMAGES));
    }

    /**
     * @return array|bool
     */
    public function getResolutions()
    {
        if ($this->getValue(self::RESOLUTIONS) !== '') {
            return explode(',', $this->getValue(self::RESOLUTIONS));
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isReplaceImagesUsingUserAgent()
    {
        return (bool)$this->isSetFlag(self::REPLACE_IMAGES_USING_USER_AGENT);
    }

    /**
     * @return array
     */
    public function getReplaceImagesUsingUserAgentIgnoreList()
    {
        return $this->convertStringToArray($this->getValue(self::REPLACE_IMAGES_USING_USER_AGENT_IGNORE_LIST));
    }

    /**
     * @return bool
     */
    public function isReplaceWithWebP()
    {
        return (bool)!$this->isSetFlag(self::REPLACE_WITH_WEBP);
    }

    /**
     * @return array
     */
    public function getReplaceIgnoreList()
    {
        return $this->convertStringToArray($this->getValue(self::REPLACE_IGNORE_IMAGES));
    }

    /**
     * @return bool
     */
    public function isSimpleOptimization()
    {
        return (int)$this->getValue(self::IMAGE_OPTIMIZATION_TYPE) === OptionSource\OptimizationSettings::SIMPLE;
    }

    public function getSkipStrategy()
    {
        return (int)$this->getValue(self::SKIP_STRATEGY);
    }

    public function getConfig($path)
    {
        return $this->getValue($path);
    }

    public function getResizeAlgorithm()
    {
        return (int)$this->getValue(self::RESIZE_ALGORITHM);
    }

    public function getBundlingType()
    {
        return (int)$this->getValue(self::BUNDLING_TYPE);
    }

    public function getBundleStep()
    {
        return (int)$this->getValue(self::BUNDLE_STEP);
    }

    public function getBundleHash()
    {
        return $this->getValue(self::BUNDLE_HASH);
    }

    /**
     * @return bool
     */
    public function isMifiniedJs()
    {
        return (bool)$this->scopeConfig->getValue('dev/js/minify_files');
    }

    /**
     * @param string $data
     * @param string $separator
     *
     * @return array
     */
    public function convertStringToArray($data, $separator = PHP_EOL)
    {
        if (empty($data)) {
            return [];
        }

        return array_filter(array_map('trim', explode($separator, $data)));
    }
}
