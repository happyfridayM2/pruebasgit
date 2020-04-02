<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Model;

/**
 * Class UrlHash
 */
class UrlHash
{
    const SALT = 'qprugn1234njd';

    /**
     * @param int $productId
     * @param string $email
     *
     * @return string
     */
    public function getHash($productId, $email)
    {
        return hash('sha256', $productId . $email . self::SALT);
    }

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @return bool
     */
    public function check(\Magento\Framework\App\Request\Http $request)
    {
        $hash = urldecode($request->getParam('hash'));
        $productId = $request->getParam('product_id');
        $email = urldecode($request->getParam('email'));

        if (empty($hash) || empty($productId) || empty($email)) {
            return false;
        }

        $real = $this->getHash($productId, $email);

        return $hash == $real;
    }
}
