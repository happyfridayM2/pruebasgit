<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Controller\Bundle;

use Amasty\PageSpeedOptimizer\Model\Bundle\BundleFactory;
use Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\Bundle as BundleResource;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Theme\Model\Theme\Resolver;

class Modules extends \Magento\Framework\App\Action\Action
{
    /**
     * @var BundleFactory
     */
    private $bundleFactory;

    /**
     * @var BundleResource
     */
    private $bundleResource;

    /**
     * @var Resolver
     */
    private $themeResolver;

    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    /**
     * @var File
     */
    private $file;

    public function __construct(
        BundleFactory $bundleFactory,
        BundleResource $bundleResource,
        Resolver $themeResolver,
        File $file,
        LocaleResolver $localeResolver,
        Context $context
    ) {
        parent::__construct($context);
        $this->bundleFactory = $bundleFactory;
        $this->bundleResource = $bundleResource;
        $this->themeResolver = $themeResolver;
        $this->localeResolver = $localeResolver;
        $this->file = $file;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        if ($data = $this->getRequest()->getParam('data')) {
            $data = json_decode($data, true);
            $theme = $this->themeResolver->get();
            foreach ($data as $item) {
                if (preg_match('/.*?(frontend|base)\/[^\/]+\/[^\/]+\/[^\/]+\/(.*)$/i', $item, $matches)) {
                    /** @var \Amasty\PageSpeedOptimizer\Model\Bundle\Bundle $file */
                    $file = $this->bundleFactory->create();
                    $file->setFilename($this->addMinifiedSign($matches[2]))
                        ->setLocale($this->localeResolver->getLocale())
                        ->setArea($theme->getArea())
                        ->setTheme($theme->getCode());

                    try {
                        $this->bundleResource->save($file);
                    } catch (\Exception $exception) {
                        null;
                    }
                }
            }
        }

        return $result->setContents('OK');
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function addMinifiedSign($filename)
    {
        $pathInfo = $this->file->getPathInfo($filename);

        if ($pathInfo['extension'] === 'js' && strpos($filename, '.min.') === false) {
            $filename = substr($filename, 0, -strlen($pathInfo['extension'])) . 'min.' . $pathInfo['extension'];
        }

        return $filename;
    }
}
