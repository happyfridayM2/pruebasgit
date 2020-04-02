<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

namespace Amasty\Shopby\Block\Navigation;

use Magento\Framework\View\Element\Template;
use Amasty\ShopbyBase\Helper\Data;

/**
 * @api
 */
class UrlModifier extends \Magento\Framework\View\Element\Template
{
    const VAR_REPLACE_URL = 'amasty_shopby_replace_url';

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'navigation/url_modifier.phtml';

    /**
     * @var \Amasty\ShopbyBase\Api\UrlBuilderInterface
     */
    private $amUrlBuilder;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\ShopbyBase\Api\UrlBuilderInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->amUrlBuilder = $urlBuilder;
    }

    public function getCurrentUrl()
    {
        $filterState = [];
        if ($this->registry->registry(Data::SHOPBY_SEO_PARSED_PARAMS)) {
            foreach ($this->registry->registry(Data::SHOPBY_SEO_PARSED_PARAMS) as $key => $item) {
                $filterState[$key] = $item;
            }
        }

        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $filterState;
        $params['_escape'] = true;
        return str_replace('&amp;', '&', $this->amUrlBuilder->getUrl('*/*/*', $params));
    }

    public function replaceUrl()
    {
        return $this->getRequest()->getParam(\Amasty\Shopby\Block\Navigation\UrlModifier::VAR_REPLACE_URL) !== null;
    }
}
