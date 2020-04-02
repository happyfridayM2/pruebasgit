<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_PageBuilder
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\PageBuilder\Instagram\Storage;

use Magezon\PageBuilder\InstagramException\CacheException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;

class CacheManager
{
    /**
     * @var string
     */
    private $cacheDir = null;

    protected $serializer;

    /**
     * CacheManager constructor.
     *
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param $userId
     *
     * @return string
     */
    private function getCacheFile($userId)
    {
        return $this->cacheDir . $userId . '.cache';
    }

    /**
     * @param $userId
     *
     * @return Cache|mixed
     */
    public function getCache($userId)
    {
        if (is_file($this->getCacheFile($userId))) {
            $handle = fopen($this->getCacheFile($userId), 'r');
            $data   = fread($handle, filesize($this->getCacheFile($userId)));
            $cache  = $this->getCacheManager()->unserialize($data);

            fclose($handle);

            if ($cache instanceof Cache) {
                return $cache;
            }

            if (is_array($cache)) {
                $newCache = new Cache();
                $newCache->setRhxGis($cache['rhxGis']);
                $newCache->setUserId($cache['userId']);
                $newCache->setCookie($cache['cookie']);
                return $newCache;
            }
        }

        return new Cache();
    }

    /**
     * @param Cache $cache
     * @param $userName
     *
     * @throws CacheException
     */
    public function set(Cache $cache, $userName)
    {
        if (!is_writable(dirname($this->getCacheFile($userName)))) {
            throw new CacheException('Cache folder is not writable');
        }

        $data   = $this->getCacheManager()->serialize($cache);
        $handle = fopen($this->getCacheFile($userName), 'w+');

        fwrite($handle, $data);
        fclose($handle);
    }



    /**
     * Retrieve cache interface
     *
     * @return CacheInterface
     * @deprecated 101.0.3
     */
    private function getCacheManager()
    {
        if (!$this->serializer) {
            $this->serializer = ObjectManager::getInstance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
        }
        return $this->serializer;
    }
}
