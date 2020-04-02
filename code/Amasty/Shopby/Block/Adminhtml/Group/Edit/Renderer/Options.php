<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Adminhtml\Group\Edit\Renderer;

/**
 * Class Options
 * @package Amasty\Shopby\Block\Adminhtml\Group\Edit\Renderer
 */
class Options extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * @var \Amasty\Shopby\Model\Source\Attribute\Option
     */
    protected $options;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var string
     */
    protected $_template = 'form/renderer/fieldset/options.phtml';

    /**
     * Options constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Amasty\Shopby\Model\Source\Attribute\Option $options
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Shopby\Model\Source\Attribute\Option $options,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->options = $options;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return string
     */
    public function getJsonOptions()
    {
        return $this->jsonEncoder->encode($this->options->toExtendedArray());
    }
}
