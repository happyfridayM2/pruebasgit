<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Adminhtml\Group\Edit;

/**
 * @api
 */
class After extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Swatches\Helper\Media
     */
    protected $mediaHelper;

    /**
     * After constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Swatches\Helper\Media $mediaHelper,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->mediaHelper = $mediaHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getJsonVisualConfig()
    {
        $data = [
            'uploadActionUrl' => $this->getUrl('swatches/iframe/show'),
            'mediaHelper' => $this->mediaHelper->getSwatchMediaUrl()
        ];

        return $this->jsonEncoder->encode($data);
    }
}
