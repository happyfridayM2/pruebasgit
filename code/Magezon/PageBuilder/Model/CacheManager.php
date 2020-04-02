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

namespace Magezon\PageBuilder\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;

class CacheManager
{
    /**
     * @var CacheInterface
     */
    private $_cacheManager;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    private $blockCollectionFactory;

    /**
     * @var \Magezon\Core\Helper\Data
     */
    private $coreHelper;

    /**
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory 
     * @param \Magezon\Core\Helper\Data                                $coreHelper             
     */
	public function __construct(
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory,
        \Magezon\Core\Helper\Data $coreHelper
	) {
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->coreHelper             = $coreHelper;
	}

	/**
	 * @return array
	 */
	public function getListBlock($cacheTag = 'listblock')
	{
		if ($cacheData = $this->getFromCache($cacheTag)) {
			return $cacheData;
		}
		$list       = [];
		$collection = $this->blockCollectionFactory->create();
    	$collection->setOrder('title', 'ASC');
    	foreach ($collection as $block) {
    		$list[] = [
				'label' => $block->getTitle(),
				'value' => $block->getId()
    		];
    	}

    	$this->saveToCache($cacheTag, $list);
    	return $list;
	}

	public function getFromCache($key)
	{
		$config = $this->getCacheManager()->load($key);
        if ($config) {
            return $this->coreHelper->unserialize($config);
        }
	}

	public function saveToCache($key, $value)
	{
		$this->getCacheManager()->save(
            $this->coreHelper->serialize($value),
            $key,
            [
                \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
            ]
        );
	}

    /**
     * Retrieve cache interface
     *
     * @return CacheInterface
     * @deprecated 101.0.3
     */
    private function getCacheManager()
    {
        if (!$this->_cacheManager) {
            $this->_cacheManager = ObjectManager::getInstance()
                ->get(CacheInterface::class);
        }
        return $this->_cacheManager;
    }
}