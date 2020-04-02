<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Framework\App\PageCache;

/**
 * Class Identifier
 * @package Amasty\Shopby\Plugin\Framework\App\PageCache
 */
class Identifier
{
    const IDENTIFIER_PREFIX = 'amasty_mobile_';

    /**
     * @var array
     */
    public static $mobileAgents = [
        'iPad',
        'iPod',
        'iPhone',
        'Android',
        'BlackBerry',
        'SymbianOS',
        'SCH-M\d+',
        'Opera Mini',
        'Windows CE',
        'Nokia',
        'SonyEricsson',
        'webOS',
        'PalmOS'
    ];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\PageCache\Model\Config $config
    ) {
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @param \Magento\Framework\App\PageCache\Identifier $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetValue(\Magento\Framework\App\PageCache\Identifier $subject, $result)
    {
        if ($this->config->getType() == \Magento\PageCache\Model\Config::BUILT_IN && $this->config->isEnabled()) {
            $userAgent = $this->request->getServer('HTTP_USER_AGENT');
            $mobileAgentsPattern = implode('|', self::$mobileAgents);
            $pattern = '/(' . $mobileAgentsPattern . ')/i';
            if (preg_match($pattern, $userAgent)) {
                $result = self::IDENTIFIER_PREFIX . $result;
            }
        }

        return $result;
    }
}
