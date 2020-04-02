<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


namespace Amasty\Base\Model;

use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class FeedContent for read file content by url
 */
class FeedContent
{
    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Zend\Uri\Uri
     */
    private $baseUrlObject;

    public function __construct(
        CurlFactory $curlFactory,
        ProductMetadataInterface $productMetadata,
        StoreManagerInterface $storeManager
    ) {
        $this->curlFactory = $curlFactory;
        $this->productMetadata = $productMetadata;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $url
     *
     * @return false|string
     */
    public function getFeedContent($url)
    {
        /** @var Curl $curlObject */
        $curlObject = $this->curlFactory->create();
        $curlObject->setConfig(
            [
                'timeout' => 2,
                'useragent' => $this->productMetadata->getName()
                    . '/' . $this->productMetadata->getVersion()
                    . ' (' . $this->productMetadata->getEdition() . ')'
            ]
        );
        $curlObject->write(\Zend_Http_Client::GET, $url, '1.0');
        $result = $curlObject->read();

        if ($result === false || $result === '') {
            return false;
        }

        $result = preg_split('/^\r?$/m', $result, 2);
        preg_match("/(?i)(\W|^)(Status: 404 File not found)(\W|$)/", $result[0], $notFoundFile);
        if ($notFoundFile) {
            return false;
        }
        $result = trim($result[1]);

        $curlObject->close();

        return $result;
    }

    /**
     * @param string $urn
     * @param bool $needFollowLocation
     *
     * @return string
     */
    public function getFeedUrl($urn, $needFollowLocation = false)
    {
        if ($needFollowLocation) {
            return 'https://' . $urn;
        }

        $scheme = $this->getCurrentScheme();
        $protocol = $scheme ?: 'http://';

        return $protocol . $urn;
    }

    /**
     * @return string
     */
    public function getCurrentScheme()
    {
        $scheme = $this->getBaseUrlObject()->getScheme();
        if ($scheme) {
            return $scheme . '://';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getDomainZone()
    {
        $host = $this->getBaseUrlObject()->getHost();
        $host = explode('.', $host);

        return end($host);
    }

    /**
     * @return \Zend\Uri\Uri
     */
    private function getBaseUrlObject()
    {
        if ($this->baseUrlObject === null) {
            $url = $this->storeManager->getStore()->getBaseUrl();
            $this->baseUrlObject = \Zend\Uri\UriFactory::factory($url);
        }

        return $this->baseUrlObject;
    }
}
