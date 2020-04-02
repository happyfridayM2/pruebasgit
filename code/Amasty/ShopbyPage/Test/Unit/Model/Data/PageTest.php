<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


namespace Amasty\ShopbyPage\Test\Unit\Model\Data;

use Amasty\ShopbyPage\Model\Data\Page;
use Amasty\ShopbyBase\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PageTest
 *
 * @see Page
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class PageTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const SITE_MEDIA = 'http://site.com/pub/media';
    const IMAGE_NAME = 'image.jpg';
    const MEDIA_ABSOLUTE_PATH = '/absolute/path/media';

    /**
     * @covers Page::getImageUrl
     */
    public function testGetImageUrl()
    {
        /** @var \Magento\Store\Model\Store|MockObject $store */
        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getBaseUrl']);
        $store->expects($this->any())->method('getBaseUrl')->willReturn(static::SITE_MEDIA);

        /** @var \Magento\Store\Model\StoreManager|MockObject $storeManager */
        $storeManager =  $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $model = $this->getObjectManager()->getObject(Page::class, ['storeManager' => $storeManager]);

        $this->assertNull($model->getImageUrl());

        $model->setImage(static::IMAGE_NAME);
        $this->assertEquals(static::SITE_MEDIA . Page::IMAGES_DIR . static::IMAGE_NAME, $model->getImageUrl());
    }

    /**
     * @covers Page::uploadImage
     *
     * @throws \ReflectionException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function testUploadImage()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write|MockObject $storeManager */
        $mediaDir = $this->createMock(\Magento\Framework\Filesystem\Directory\Write::class);
        $mediaDir->expects($this->once())->method('getAbsolutePath')->willReturn(static::MEDIA_ABSOLUTE_PATH);

        /** @var \Magento\Framework\Filesystem|MockObject $storeManager */
        $filesystem =  $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystem->expects($this->any())->method('getDirectoryWrite')->willReturn($mediaDir);

        $uploader = $this->createPartialMock(\Magento\Framework\File\Uploader::class, ['save', 'getUploadedFileName']);
        $uploader->expects($this->once())->method('save');
        $uploader->expects($this->once())->method('getUploadedFileName')->willReturn(static::IMAGE_NAME);

        $uploaderFactory = $this->createPartialMock(\Magento\Framework\File\UploaderFactory::class, ['create']);
        $uploaderFactory->expects($this->once())->method('create')->willReturnReference($uploader);

        /** @var Page|MockObject $model */
        $model = $this->createPartialMock(Page::class, ['removeImage']);
        $model->expects($this->once())->method('removeImage');

        $this->setProperty($model, 'fileSystem', $filesystem, Page::class);
        $this->setProperty($model, 'uploaderFactory', $uploaderFactory, Page::class);

        $this->assertEquals(static::IMAGE_NAME, $model->uploadImage(1));
        $this->assertEquals(false, $this->getProperty($uploader, '_enableFilesDispersion'));
        $this->assertEquals(false, $this->getProperty($uploader, '_caseInsensitiveFilenames'));
        $this->assertEquals(true, $this->getProperty($uploader, '_allowRenameFiles'));
        $this->assertEquals(['jpg', 'png', 'jpeg', 'gif', 'bmp', 'svg'], $this->getProperty($uploader, '_allowedExtensions'));
    }

    /**
     * @covers Page::getImagePath
     */
    public function testGetImagePath()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Read|MockObject $storeManager */
        $mediaDir = $this->createMock(\Magento\Framework\Filesystem\Directory\Read::class);
        $mediaDir->expects($this->once())->method('getAbsolutePath')->willReturnCallback(
            function ($imagePath) {
                return static::MEDIA_ABSOLUTE_PATH . $imagePath;
            }
        );

        /** @var \Magento\Framework\Filesystem|MockObject $storeManager */
        $filesystem =  $this->createMock(\Magento\Framework\Filesystem::class);
        $filesystem->expects($this->any())->method('getDirectoryRead')->willReturn($mediaDir);

        /** @var Page $model */
        $model = $this->getObjectManager()->getObject(Page::class, ['fileSystem' => $filesystem]);
        $model->setImage(static::IMAGE_NAME);
        $this->assertEquals(static::MEDIA_ABSOLUTE_PATH . Page::IMAGES_DIR . static::IMAGE_NAME, $model->getImagePath());
    }
}
