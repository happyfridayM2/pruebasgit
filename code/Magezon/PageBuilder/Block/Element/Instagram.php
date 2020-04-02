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

namespace Magezon\PageBuilder\Block\Element;

use Magento\Framework\App\Filesystem\DirectoryList;

class Instagram extends \Magezon\Builder\Block\Element
{
	const INSTAGRAM_USERNAME_ITEMS = 12;
	const INSTAGRAM_HASHTAG_ITEMS  = 71;

	/**
	 * @var \Magento\Framework\Filesystem
	 */
	protected $_filesystem;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context    
	 * @param array                                            $data       
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = []
	) {
		parent::__construct($context, $data);
		$this->_filesystem = $context->getFilesystem();
	}

	/**
	 * @return array
	 */
	public function getItems()
	{
		$element  = $this->getElement();
		$maxItems = $element->getData('max_items');
		$type     = $element->getData('fetch_type');
		$key      = $element->getData('fetch_key');
		$items    = [];

		try {
			if ($type) {

				$mediaPath = $this->_filesystem->getDirectoryRead(DirectoryList::CACHE)->getAbsolutePath('/magezon/instagram');

				if (!file_exists($mediaPath)) mkdir($mediaPath, 0777, true);

				$cache = new \Magezon\PageBuilder\Instagram\Storage\CacheManager($mediaPath);
				$api   = new \Magezon\PageBuilder\Instagram\Api($cache);
				$api->setKey($key);
				$api->setType($type);

				$feed     = $api->getFeed();
				$index    = 0;
				$continue = true;
				while($continue) {

					if ($index==0) {
						$feed = $api->getFeed();
					} else {
						$endCursor = $feed->getEndCursor();
						$api->setEndCursor($endCursor);
						$feed = $api->getFeed();
					}

					$photos = $this->getPhotos($feed);
					$index++;

					if ($type=='hashtag') {
						if (count($photos) < self::INSTAGRAM_HASHTAG_ITEMS) {
							$continue = false;
						}

					} else {
						if (count($photos) < self::INSTAGRAM_USERNAME_ITEMS) {
							$continue = false;
						}
					}

					foreach ($photos as $photo) {
						$items[] = $photo;
						if (count($items)==$maxItems) {
							$continue = false;
							break;
						}
					}
				}
			}
		} catch (\Exception $e) {
		}
		return $items;
	}

	/**
	 * @return array
	 */
	public function getPhotos($feed)
	{
		$photos = [];
		foreach ($feed->getMedias() as $media) {
	    	$thumbnails = $media->getThumbnails();
	    	$photos[] = array(
				'id'        => $media->getId(),
				'caption'   => $media->getCaption(),
				'link'      => $media->getLink(),
				'comments'  => $media->getComments(),
				'likes'     => $media->getLikes(),
				'thumbnail' => $media->getThumbnails()[0]->src,
				'small'     => $media->getThumbnails()[2]->src,
				'large'     => $media->getThumbnails()[4]->src,
				'original'  => $media->getDisplaySrc(),
				'Date'      => $media->getDate()->format('Y-m-d h:i:s'),
				'type'      => $media->isVideo() ? 'video' : 'image'
			);
	    }
	    return $photos;
	}

	/**
	 * @return string
	 */
	public function getFollowLink()
	{
		$element   = $this->getElement();
		$fetchType = $element->getData('fetch_type');
		$fetchKey  = $element->getData('fetch_key');
		if ($fetchType == 'hashtag') {
			return 'https://instagram.com/explore/tags/' . $fetchKey;
		} else {
			return 'https://www.instagram.com/' . $fetchKey;
		}
	}

	/**
	 * @return string
	 */
	public function getDataSize()
	{
		$element   = $this->getElement();
		$photoSize = $element->getData('photo_size');
		$size      = '1000x1000';
		switch ($photoSize) {
			case 'thumbnail':
				$size = '150x150';
				break;

			case 'small':
				$size = '320x320';
				break;

			case 'large':
				$size = '640x640';
				break;
		}
		return $size;
	}

	/**
	 * @return string
	 */
	public function getAdditionalStyleHtml()
	{
		$styleHtml = '';
		$element   = $this->getElement();

		if ($gap = (int)$element->getData('gap')) {
			$styles = [];
			$styles['padding'] = $this->getStyleProperty($gap / 2);
			$styleHtml .= $this->getStyles([
				'.mgz-grid-item'
			], $styles);
		}

		$styles = [];
		$styles['font-size'] = $this->getStyleProperty($element->getData('text_size'));
		$styles['color'] = $this->getStyleColor($element->getData('text_color'));
		$styleHtml .= $this->getStyles(['.item-likes', '.item-comments'], $styles);

		$styles = [];
		$styles['background'] = $this->getStyleColor($element->getData('overlay_color'));
		$styleHtml .= $this->getStyles('.item-metadata', $styles);

		$styleHtml .= $this->getLineStyles();

		return $styleHtml;
	}
}