<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magmodules\AlternateHreflang\Helper\Category as CateroryHelper;
use Magmodules\AlternateHreflang\Helper\General as GeneralHelper;

/**
 * Class Category
 *
 * @package Magmodules\AlternateHreflang\Block
 */
class Category extends Template
{

    /**
     * @var CateroryHelper
     */
    private $categoryHelper;
    /**
     * @var GeneralHelper
     */
    private $generalHelper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Category constructor.
     *
     * @param Context        $context
     * @param CateroryHelper $categoryHelper
     * @param GeneralHelper  $generalHelper
     * @param array          $data
     */
    public function __construct(
        Context $context,
        CateroryHelper $categoryHelper,
        GeneralHelper $generalHelper,
        array $data = []
    ) {
        $this->request = $context->getRequest();
        $this->categoryHelper= $categoryHelper;
        $this->generalHelper = $generalHelper;
        parent::__construct($context, $data);
    }

    /**
     * Gets alternate data from category helper
     *
     * @return array
     */
    public function getAlternateData()
    {
        return $this->categoryHelper->getAlternateData();
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
