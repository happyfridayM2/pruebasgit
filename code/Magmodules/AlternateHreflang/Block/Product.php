<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\AlternateHreflang\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magmodules\AlternateHreflang\Helper\Product as ProductHelper;
use Magmodules\AlternateHreflang\Helper\General as GeneralHelper;

/**
 * Class Product
 *
 * @package Magmodules\AlternateHreflang\Block
 */
class Product extends Template
{

    /**
     * @var ProductHelper
     */
    private $productHelper;
    /**
     * @var GeneralHelper
     */
    private $generalHelper;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Product constructor.
     *
     * @param Context       $context
     * @param ProductHelper $productHelper
     * @param GeneralHelper $generalHelper
     * @param array         $data
     */
    public function __construct(
        Context $context,
        ProductHelper $productHelper,
        GeneralHelper $generalHelper,
        array $data = []
    ) {
        $this->request = $context->getRequest();
        $this->productHelper = $productHelper;
        $this->generalHelper = $generalHelper;
        parent::__construct($context, $data);
    }

    /**
     * Gets alternate data from product helper
     *
     * @return array|bool
     */
    public function getAlternateData()
    {
        return $this->productHelper->getAlternateData();
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
