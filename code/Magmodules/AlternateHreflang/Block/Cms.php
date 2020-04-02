<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magmodules\AlternateHreflang\Helper\Cms as CmsHelper;
use Magmodules\AlternateHreflang\Helper\General as GeneralHelper;

/**
 * Class Homepage
 *
 * @package Magmodules\AlternateHreflang\Block
 */
class Cms extends Template
{

    /**
     * @var CmsHelper
     */
    private $cmsHelper;
    /**
     * @var GeneralHelper
     */
    private $generalHelper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Homepage constructor.
     *
     * @param Context       $context
     * @param CmsHelper     $cmsHelper
     * @param GeneralHelper $generalHelper
     * @param array         $data
     */
    public function __construct(
        Context $context,
        CmsHelper $cmsHelper,
        GeneralHelper $generalHelper,
        array $data = []
    ) {
        $this->request = $context->getRequest();
        $this->cmsHelper = $cmsHelper;
        $this->generalHelper = $generalHelper;
        parent::__construct($context, $data);
    }

    /**
     * Gets alternate data from cms helper
     *
     * @return array
     */
    public function getAlternateData()
    {
        return $this->cmsHelper->getAlternateData();
    }

    /**
     * Checks if debug message must be displayed
     *
     * @return bool
     */
    public function getAlternateDebug()
    {
        $showDebug = $this->request->getParam('show-alternate');
        if ($showDebug) {
            return $this->generalHelper->getAlternateDebug();
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function showCommentTags()
    {
        return $this->generalHelper->getAlternateDebug();
    }
}
