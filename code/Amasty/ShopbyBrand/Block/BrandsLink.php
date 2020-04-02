<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Block;

/**
 * Class BrandsLink
 *
 * @package Amasty\ShopbyBrand\Block
 */
class BrandsLink extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $helper;

    /**
     * @var string
     */
    protected $_template = 'brands_link.phtml';

    /**
     * BrandsLink constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amasty\ShopbyBrand\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\ShopbyBrand\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getAllBrandsUrl()
    {
        return $this->helper->getAllBrandsUrl();
    }
}
