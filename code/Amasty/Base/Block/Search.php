<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


namespace Amasty\Base\Block;

use Amasty\Base\Helper\Module;
use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Search block
 */
class Search extends Template implements RendererInterface
{
    protected $_template = 'Amasty_Base::search.phtml';

    /**
     * @var Module
     */
    private $moduleHelper;

    public function __construct(
        Template\Context $context,
        Module $moduleHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->moduleHelper = $moduleHelper;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setData('cache_lifetime', 86400);
    }

    /**
     * Render Search html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return string
     */
    public function getSearchBaseUrl()
    {
        $baseUrl = 'https://amasty.com/catalogsearch/result/?q=';

        if ($this->moduleHelper->isOriginMarketplace()) {
            $baseUrl = 'https://marketplace.magento.com/catalogsearch/result/?q=Amasty%20';
        }

        return $baseUrl;
    }

    /**
     * @return string
     */
    public function getSearchUrlParams()
    {
        $params = '&utm_source=extension&utm_medium=extnotif&utm_campaign=searchbar';

        if ($this->moduleHelper->isOriginMarketplace()) {
            $params = '&categories=Extensions&ext_platform=Magento%202';
        }

        return $params;
    }
}
