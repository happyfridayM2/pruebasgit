<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\UrlRewrite\Model\Storage;

use Magento\UrlRewrite\Model\Storage\DbStorage as UrlRewriteDbStorage;
use Amasty\ShopbyBase\Helper\Data;

/**
 * Class DbStorage
 * @package Amasty\Shopby\Plugin\UrlRewrite\Model\Storage
 */
class DbStorage
{
    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    private $baseHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Amasty\ShopbyBase\Helper\Data $baseHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->baseHelper = $baseHelper;
        $this->registry = $registry;
    }

    /**
     * @param UrlRewriteDbStorage $subject
     * @param callable $proceed
     * @param array $data
     * @return null
     */
    public function aroundFindOneByData(UrlRewriteDbStorage $subject, callable $proceed, array $data)
    {
        $identifier = isset($data['request_path']) ? $data['request_path'] : null;
        $urlKey = trim($this->baseHelper->getBrandUrlKey());

        if ($urlKey && $urlKey == $identifier && $this->registry->registry(Data::SHOPBY_SEO_PARSED_PARAMS)) {
            return null;
        }

        return $proceed($data);
    }
}
