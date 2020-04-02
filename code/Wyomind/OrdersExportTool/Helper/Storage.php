<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Helper;

/**
 * Class Storage
 * @package Wyomind\OrdersExportTool\Helper
 */
class Storage extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|null
     */
    protected $_ioWrite=null;
    /**
     * @var \Magento\Framework\Filesystem\Io\Ftp|null
     */
    protected $_ioFtp=null;
    /**
     * @var \Magento\Framework\Filesystem\Io\Sftp|null
     */
    protected $_ioSftp=null;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList|null
     */
    protected $_directoryList=null;
    /**
     * @var \Magento\Framework\Message\ManagerInterface|null
     */
    protected $_messageManager=null;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|null
     */
    protected $_dateTime=null;
    /**
     * @var \Magento\Store\Model\StoreManager|null
     */
    protected $_storeManager=null;
    /**
     * @var null|\Wyomind\Core\Helper\Data
     */
    public $coreHelper=null;
    /**
     * @var array
     */
    protected $_ext=[
        1=>'xml',
        2=>'txt',
        3=>'csv',
        4=>'tsv',
        5=>'din'
    ];
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * Storage constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem\Io\Ftp $ioFtp
     * @param \Magento\Framework\Filesystem\Io\Sftp $ioSftp
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\Io\Ftp $ioFtp,
        \Magento\Framework\Filesystem\Io\Sftp $ioSftp,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Wyomind\Core\Helper\Data $coreHelper
    )
    {
        $this->_ioFtp=$ioFtp;
        $this->_ioSftp=$ioSftp;
        $this->_ioWrite=$filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->_messageManager=$messageManager;
        $this->_dateTime=$dateTime;
        $this->_storeManager=$storeManager;
        $this->coreHelper=$coreHelper;
        parent::__construct($context);
        $this->directoryList=$directoryList;
    }


    /**
     * Get file type
     * @param string $type
     * @return string
     */
    public function getFileType($type)
    {
        return $this->_ext[$type];
    }

    /**
     * Get the file name
     * @param $dateFormat
     * @param $name
     * @param string $type
     * @param $currentTime
     * @param string $temp
     * @param null|string $increment
     * @return string
     */
    public function getFileName($dateFormat, $name, $type, $currentTime, $temp='.temp', $increment=null)
    {
        $nameTmp=$this->_dateTime->date($dateFormat, $currentTime);
        $fileNameOutput=str_replace('{f}', $name, $nameTmp);
        return $fileNameOutput . $increment . "." . $this->getFileType($type) . $temp;
    }

    /**
     * Return the file name
     * @param object $model
     * @return string|string[]|null
     */
    public function getFile($model)
    {
        $types=[
            'none',
            'xml',
            'txt',
            'csv',
            'tsv',
            'din'
        ];
        $ext=$types[$model->getType()];
        $date=$this->_dateTime->date($model->getDateFormat(), strtotime($model->getUpdatedAt()));
        $fileName=preg_replace('/^\//', '', $model->getPath() . str_replace('{f}', $model->getName(), $date) . '.' . $ext);

        return $fileName;
    }

    /**
     * Return the file url
     * @param string $file
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFileUrl($file)
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . $file;
    }

    /**
     * Open a file with write permission
     * @param string $path
     * @param string $file
     * @return file interface
     * @throws \Exception
     */
    public function openDestinationFile($path, $file)
    {
        $io=null;
        $this->_ioWrite->create($path);

        if (!$this->_ioWrite->isWritable($path)) {
            throw new \Exception(__('File "%1" cannot be saved.<br/>Please, make sure the directory "%2" is writable by web server.', $file, $path));
        } else {
            $io=$this->_ioWrite->openFile($path . $file, 'w');
        }

        return $io;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getAbsoluteRootDir()
    {

        $rootDirectory=$this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        return $rootDirectory;
    }

}