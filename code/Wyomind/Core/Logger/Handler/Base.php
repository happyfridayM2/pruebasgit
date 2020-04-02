<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Core\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Class Base
 * @package Wyomind\Core\Logger\Handler
 */
class Base extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @param DriverInterface $filesystem
     * @param string $filePath
     * @param string $fileName
     * @param \Magento\Framework\App\ProductMetadata $productMetaData
     */
    public function __construct(
        DriverInterface $filesystem,
        $filePath=null,
        $fileName=null,
        \Magento\Framework\App\ProductMetadata $productMetaData
    )
    {
        $explodedVersion=explode("-", $productMetaData->getVersion());
        $magentoVersion=$explodedVersion[0];
        if (version_compare($magentoVersion, "2.2.0", "<")) {

            $filePath=BP . $fileName;
            parent::__construct($filesystem, $filePath);
        } else {
           
            parent::__construct($filesystem, $filePath, $fileName);
        }
    }
}