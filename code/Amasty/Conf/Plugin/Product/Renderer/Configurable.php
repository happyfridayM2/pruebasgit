<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
namespace Amasty\Conf\Plugin\Product\Renderer;

class Configurable
{
    /**
     * @var \Amasty\Conf\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    public function __construct(
        \Amasty\Conf\Helper\Data $helper,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->helper = $helper;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @param $subject
     * @param $result
     * @return string
     */
    public function afterToHtml(
        $subject,
        $result
    ) {
        $layout = $this->layoutFactory->create();
        $block = $layout->createBlock(
            \Amasty\Conf\Block\Product\Renderer::class,
            'amasty.conf.renderer',
            [ 'data' => [] ]
        );

        $html = $block->toHtml();
        $result = $html . $result;

        return  $result;
    }
}
