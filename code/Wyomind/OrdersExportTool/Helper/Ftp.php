<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Helper;

/**
 * Class Ftp
 * @package Wyomind\OrdersExportTool\Helper
 */
class Ftp extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Filesystem\Io\Ftp|null
     */
    protected $_ioFtp=null;
    /**
     * @var \Magento\Framework\Filesystem\Io\Sftp|null
     */
    protected $_ioSftp=null;
    /**
     * @var Storage
     */
    protected $storageHelper;
    /**
     * @var \Wyomind\Core\Helper\Data
     */
    private $coreHelper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * Ftp constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param Storage $storageHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Filesystem\Io\Ftp $ioFtp
     * @param \Magento\Framework\Filesystem\Io\Sftp $ioSftp
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Wyomind\Core\Helper\Data $coreHelper,
        \Wyomind\OrdersExportTool\Helper\Storage $storageHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem\Io\Ftp $ioFtp,
        \Magento\Framework\Filesystem\Io\Sftp $ioSftp
    )
    {

        parent::__construct($context);
        $this->_ioFtp=$ioFtp;
        $this->_ioSftp=$ioSftp;
        $this->coreHelper=$coreHelper;
        $this->messageManager=$messageManager;
        $this->storageHelper = $storageHelper;
    }

    /**
     * @param $data
     * @return \Magento\Framework\Filesystem\Io\Ftp|\Magento\Framework\Filesystem\Io\Sftp|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConnection($data)
    {
        $port=$data['ftp_port'];
        $login=$data['ftp_login'];
        $password=$data['ftp_password'];
        $sftp=$data['use_sftp'];
        $active=$data['ftp_active'];

        $host=str_replace(["ftp://", "ftps://"], "", $data["ftp_host"]);
        if ($data['ftp_port'] != "" && $data["use_sftp"]) {
            $host.=":" . $data['ftp_port'];
        }
        if (isset($data['file_path'])) {
            $fullFilePath=rtrim($data['ftp_dir'], "/") . "/" . ltrim($data['file_path'], "/");
            $fullPath=dirname($fullFilePath);
        } else {
            $fullPath=rtrim($data['ftp_dir'], "/");
        }


        if ($sftp) {
            $ftp=$this->_ioSftp;
        } else {
            $ftp=$this->_ioFtp;
        }
        $ftp->open(
            array(
                'host'=>$host,
                'port'=>$port,
                'user'=>$login, //ftp
                'username'=>$login, //sftp
                'password'=>$password,
                'timeout'=>'10',
                'path'=>$fullPath,
                'passive'=>!($active)
            )
        );

        // sftp doesn't chdir automatically when opening connection
        if ($sftp) {
            $ftp->cd($fullPath);
        }

        return $ftp;
    }

    /**
     * @param $useSftp
     * @param $ftpPassive
     * @param $ftpHost
     * @param $ftpPort
     * @param $ftpLogin
     * @param $ftpPassword
     * @param $ftpDir
     * @param $path
     * @param $file
     * @return bool
     */

    public function ftpUpload($useSftp, $ftpPassive, $ftpHost, $ftpPort, $ftpLogin, $ftpPassword, $ftpDir, $path, $file)
    {
        if ($useSftp) {
            $ftp=$this->_ioSftp;
        } else {
            $ftp=$this->_ioFtp;
        }

        $rtn=false;
        try {
            $host=str_replace(["ftp://", "ftps://"], "", $ftpHost);

            $ftp->open([
                'host'=>$host,
                'port'=>$ftpPort, // only ftp
                'user'=>$ftpLogin,
                'username'=>$ftpLogin, // only sftp
                'password'=>$ftpPassword,
                'timeout'=>'120',
                'path'=>$ftpDir,
                'passive'=>$ftpPassive // only ftp
            ]);

            if ($useSftp) {
                $ftp->cd($ftpDir);
            }

            if (!$useSftp && $ftp->write($file, $this->storageHelper->getAbsoluteRootDir() . $path . $file)) {
                if ($this->coreHelper->isAdmin()) {
                    $this->messageManager->addSuccess(sprintf(__("File '%s' successfully uploaded on %s"), $file, $ftpHost) . ".");
                }
                $rtn=true;
            } elseif ($useSftp && $ftp->write($file, $this->storageHelper->getAbsoluteRootDir() . $path . $file)) {
                if ($this->coreHelper->isAdmin()) {
                    $this->messageManager->addSuccess(sprintf(__("File '%s' successfully uploaded on %s"), $file, $ftpHost) . ".");
                }
                $rtn=true;
            } else {
                if ($this->coreHelper->isAdmin()) {
                    $this->messageManager->addError(sprintf(__("Unable to upload '%s'on %s"), $file, $ftpHost) . ".");
                }
                $rtn=false;
            }
        } catch (\Exception $e) {
            if ($this->coreHelper->isAdmin()) {
                $this->messageManager->addError(__("Ftp upload error : ") . $e->getMessage());
            }
        }
        $ftp->close();
        return $rtn;
    }

}
