<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Filesystem;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Compressor
 *
 * @package Amasty\Feed
 */
class Compressor
{
    /**
     * @var \Magento\Framework\Archive\ArchiveInterface[]
     */
    private $compressors;

    public function __construct($compressors)
    {
        $this->compressors = $compressors;
    }

    /**
     * @param string $compressor
     * @param string $source
     * @param string $destination
     *
     * @return string
     * @throws LocalizedException
     */
    public function pack($compressor, $source, $destination)
    {
        if (!isset($this->compressors[$compressor])) {
            throw new LocalizedException(__('Unknown compressor'));
        }

        if ($compressor !== 'zip') {
            return $this->compressors[$compressor]->pack($source, $destination);
        } else {
            /** Magento Zip Archive packs with path folders. Fixed with second param in addFile */
            //phpcs:disable
            $zip = new \ZipArchive();
            $zip->open($destination, \ZipArchive::CREATE);
            $zip->addFile($source, basename($source));
            $zip->close();
            //phpcs:enable
            return $destination;
        }
    }
}
