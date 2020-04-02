<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Adminhtml\Product\Attribute\Edit\Tab\Shopby;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class Multiselect
 * @package Amasty\Shopby\Block\Adminhtml\Product\Attribute\Edit\Tab\Shopby
 */
class Multiselect extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements RendererInterface
{
    protected $_template = 'form/renderer/fieldset/multiselect.phtml';
}
