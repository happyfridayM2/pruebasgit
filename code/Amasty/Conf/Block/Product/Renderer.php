<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Block\Product;

class Renderer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Conf\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Conf\Helper\Data $helper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->setTemplate('Amasty_Conf::product/view/renderer.phtml');
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return \Amasty\Conf\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        $config = [
            'share'           =>  [
                'enable' => $this->helper->getModuleConfig('general/share'),
                'title'  => __('Share'),
                'link'   => __('COPY')
            ]
        ];

        return $this->jsonEncoder->encode($config);
    }
}
