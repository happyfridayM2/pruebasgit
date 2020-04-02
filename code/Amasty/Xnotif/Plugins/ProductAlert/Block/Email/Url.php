<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\ProductAlert\Block\Email;

/**
 * Class Url
 */
class Url
{
    /**
     * @var int
     */
    private $productId;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Xnotif\Model\UrlHash
     */
    private $urlHash;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\Xnotif\Model\UrlHash $urlHash
    ) {
        $this->registry = $registry;
        $this->urlHash = $urlHash;
    }

    /**
     * @param $subject
     * @return null|string
     */
    private function getType($subject)
    {
        $type = null;
        if ($subject instanceof \Magento\ProductAlert\Block\Email\Price) {
            $type = 'price';
        }
        if ($subject instanceof \Magento\ProductAlert\Block\Email\Stock) {
            $type = 'stock';
        }

        return $type;
    }

    /**
     * @param $subject
     * @param string $route
     * @param array $params
     * @return array
     */
    public function beforeGetUrl($subject, $route = '', $params = [])
    {
        if ($data = $this->registry->registry('amxnotif_data')) {
            $type = $this->getType($subject);
            if ($type && isset($data['guest']) && isset($data['email'])) {
                $hash = $this->urlHash->getHash(
                    $this->productId,
                    $data['email']
                );
                $params['product_id'] = $this->getProductId();
                $params['email'] = urlencode($data['email']);
                $params['hash'] = urlencode($hash);
                $params['type'] = $type;
            }
        }

        return [$route, $params];
    }

    /**
     * @param $subject
     * @param $productId
     */
    public function beforeGetProductUnsubscribeUrl($subject, $productId)
    {
        $this->setProductId($productId);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }
}
