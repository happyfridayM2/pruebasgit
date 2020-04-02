<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Filesystem;

use Amasty\Base\Model\MagentoVersion;
use Amasty\Feed\Api\Data\FeedInterface;
use Magento\Framework\Exception\LocalizedException;

class Ftp
{
    /**
     * @var \Magento\Framework\Filesystem\Io\Ftp
     */
    private $ftp;

    /**
     * @var \Magento\Framework\Filesystem\Io\Sftp
     */
    private $sftp;

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var FeedOutput
     */
    private $feedOutput;

    public function __construct(
        \Magento\Framework\Filesystem\Io\Ftp $ftp,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        MagentoVersion $magentoVersion,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Amasty\Feed\Model\Filesystem\FeedOutput $feedOutput
    ) {
        $this->ftp = $ftp;
        $this->sftp = $sftp;
        $this->magentoVersion = $magentoVersion;
        $this->encryptor = $encryptor;
        $this->feedOutput = $feedOutput;
    }

    public function ftpUpload(FeedInterface $feed)
    {
        $feedOutput = $this->feedOutput->get($feed);

        if (strpos($feed->getDeliveryHost(), ':') !== false) {
            list($host, $port) = explode(':', $feed->getDeliveryHost(), 2);
        } else {
            $host = $feed->getDeliveryHost();
            $port = null;
        }

        $this->ftp->open(
            [
                'host' => $host,
                'port' => $port,
                'user' => $feed->getDeliveryUser(),
                'password' => $this->encryptor->decrypt($feed->getDeliveryPassword()),
                'passive' => $feed->getDeliveryPassiveMode(),
                'path' => $feed->getDeliveryPath()
            ]
        );
        $this->ftp->write($feed->getFilename(), $feedOutput['absolute_path']);
        $this->ftp->close();
    }

    public function sftpUpload(FeedInterface $feed)
    {
        $feedOutput = $this->feedOutput->get($feed);

        if (version_compare($this->magentoVersion->get(), '2.2.0', '<')) {
            /** Fix for Magento <2.2.0 versions @see https://github.com/magento/magento2/issues/9016 */
            define('NET_SFTP_LOCAL_FILE', \phpseclib\Net\SFTP::SOURCE_LOCAL_FILE);
            define('NET_SFTP_STRING', \phpseclib\Net\SFTP::SOURCE_STRING);
        }

        $this->sftp->open(
            [
                'host' => $feed->getDeliveryHost(),
                'username' => $feed->getDeliveryUser(),
                'password' => $this->encryptor->decrypt($feed->getDeliveryPassword()),
            ]
        );

        $path = $this->sftp->cd($feed->getDeliveryPath() ?: '');

        if (!$path) {
            $this->sftp->close();
            throw new LocalizedException(__('Invalid path'));
        }

        $this->sftp->write($feed->getFilename(), $feedOutput['absolute_path']);
        $this->sftp->close();
    }
}
