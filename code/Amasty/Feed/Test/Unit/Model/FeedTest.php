<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Test\Unit\Model;

use Amasty\Feed\Model\Feed;
use Amasty\Feed\Test\Unit\Traits;

/**
 * Class FeedTest
 *
 * @see Feed
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FeedTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers Feed::getFilename
     *
     * @dataProvider getFilenameDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetFilename($file, $type)
    {
        /** @var Feed $model */
        $model = $this->getObjectManager()->getObject(Feed::class);
        $model->setData('filename', $file);
        $model->setData('feed_type', $type);

        $this->assertEquals($file . '.' . $type, $model->getFilename());
    }

    /**
     * Data provider for getFilename test
     * @return array
     */
    public function getFilenameDataProvider()
    {
        return [
            ['testfile1', 'csv'],
            ['testfile2', 'txt'],
            ['testfile3', 'xml']
        ];
    }
}
