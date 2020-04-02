<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Framework\Search\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;

/**
 * Class Config
 * @package Amasty\Shopby\Plugin\Framework\Search\Request
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    public function __construct(
        ScopeConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * @param \Magento\Framework\Search\Request\Config $subject
     * @param array $result
     * @return array
     */
    public function afterGet(\Magento\Framework\Search\Request\Config $subject, $result)
    {
        if ($this->config->getValue(EngineProvider::CONFIG_ENGINE_PATH) == 'mysql') {
            if (isset($result['query']) && isset($result['size'])
                && in_array($result['query'], ['catalog_view_container', 'quick_search_container'], true)
            ) {
                //Extend result size if default.
                $result['size'] = strcmp($result['size'], '10000') === 0
                    ? '100000'
                    : $result['size'];
            }
        }

        return $result;
    }
}
