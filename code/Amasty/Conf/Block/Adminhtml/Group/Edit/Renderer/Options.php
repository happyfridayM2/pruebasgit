<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Block\Adminhtml\Group\Edit\Renderer;

class Options extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * @var \Amasty\Conf\Model\Source\Attribute\Option
     */
    private $options;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    protected $_template = 'Amasty_Conf::form/renderer/fieldset/options.phtml';

    /**
     * Options constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Amasty\Conf\Model\Source\Attribute\Option $options
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Conf\Model\Source\Attribute\Option $options,
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
