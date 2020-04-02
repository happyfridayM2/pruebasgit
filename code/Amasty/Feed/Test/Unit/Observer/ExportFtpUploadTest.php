<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Test\Unit\Observer;

use Amasty\Feed\Observer\ExportFtpUpload;
use Amasty\Feed\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class ExportFtpUploadTest
 *
 * @see ExportFtpUpload
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ExportFtpUploadTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers ExportFtpUpload::execute
     *
     * @dataProvider executeDataProvider
     *
     * @throws \ReflectionException
     */
    public function testExecute($protocol, $method, $expectedResult, $isDeliveryEnable = true)
    {
        /** @var \Amasty\Feed\Model\Filesystem\Ftp|MockObject $ftp */
        $ftp = $this->createMock(\Amasty\Feed\Model\Filesystem\Ftp::class);
        $ftp->expects($this->any())->method('sftpUpload')->willReturn(true);
        $ftp->expects($this->any())->method('ftpUpload')->willReturn(true);
        /** @var \Magento\Framework\Event\Observer $observer */
        $observer = $this->getObjectManager()->getObject(\Magento\Framework\Event\Observer::class);
        /** @var \Amasty\Feed\Api\Data\FeedInterface|MockObject $feed */
        $feed = $this->createMock(\Amasty\Feed\Api\Data\FeedInterface::class);
        $feed->expects($this->any())->method('getDeliveryEnabled')->willReturn($isDeliveryEnable);
        $feed->expects($this->any())->method('getDeliveryType')->willReturn($protocol);

        $observer->setData('feed', $feed);
        /** @var ExportFtpUpload $observer */
        $exportFtpUpload = $this->getObjectManager()->getObject(ExportFtpUpload::class);

        $this->setProperty($exportFtpUpload, 'ftp', $ftp, ExportFtpUpload::class);

        $ftp->expects($this->exactly($expectedResult))->method($method);
        $exportFtpUpload->execute($observer);
    }

    /**
     * Data provider for execute test
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            ['ftp', 'ftpUpload', 1],
            ['sftp', 'sftpUpload', 1],
            ['sftpTest', 'sftpUpload', 0, false],
            ['ftpTest', 'ftpUpload', 0, false],
            ['ftp', 'ftpUpload', 0, false],
            ['sftp', 'sftpUpload', 0, false]
        ];
    }
}
