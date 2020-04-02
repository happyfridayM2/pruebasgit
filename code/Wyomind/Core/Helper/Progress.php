<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Wyomind\Core\Helper;
/**
 * Class Progress
 * @package Wyomind\Core\Helper
 */
class Progress extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * DEBUG MODE ENABLED?
     */
    const DEBUG = false;
    /**
     *
     */
    const SUCCEEDED = 'SUCCEEDED';
    /**
     *
     */
    const PENDING = 'PENDING';
    /**
     *
     */
    const PROCESSING = 'PROCESSING';
    /**
     *
     */
    const HOLD = 'HOLD';
    /**
     *
     */
    const FAILED = 'FAILED';
    /**
     *
     */
    const ERROR = 'ERROR';
    /**
     * @var string
     */
    private $_flagFile;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $_ioWrite;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $_ioRead;
    /**
     * {@inherit}
     */
    private $logger;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;
    /**
     * Module name
     * @var string
     */
    private $module;
    /**
     * Path to the tmp directory
     * @var null
     */
    private $tempDirectory;
    /**
     * Prefix of the flag name
     * @var null
     */
    private $filePrefix;

    /**
     * Is log file enabled
     * @var bool
     */
    private $_logEnabled = true;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * Progress constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param string $module
     * @param string $tempDirectory
     * @param string $filePrefix
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        $module = "Core",
        $tempDirectory = "var/tmp/wyomind",
        $filePrefix = "item_"

    )
    {


        $this->filesystem = $filesystem;
        $this->objectManager = $objectManager;

        $this->_ioWrite = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->_ioRead = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);

        $this->logger = $objectManager->create("\Wyomind\\" . $module . "\Logger\Logger");

        $this->directoryList = $directoryList;
        $this->module = $module;
        $this->tempDirectory = $tempDirectory;
        $this->filePrefix = $filePrefix;
        $this->dateTime = $dateTime;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getAbsoluteRootDir()
    {
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
    }

    /**
     * @param $logEnabled
     * @param string $file
     * @param string $name
     * @param bool $byPass
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function startObservingProgress($logEnabled, $file = "progress", $name = "profile", $byPass = false)
    {

        $this->_logEnabled = $logEnabled;

//        set_error_handler(array($this, "shutdown"));
//        register_shutdown_function(array($this, "shutdown"));

        $line = $this->readFlag($file);
        if (!$byPass) {
            if ($line["status"] === self::PROCESSING) {
                $this->flagUpdate(self::ERROR . ";" . __('"%1" is already processing. Please wait the end of the process.', $name) . ";0");
                if (php_sapi_name() === 'cli') {

                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(__('"%1" is already processing. Please wait the end of the process.', $name));

                }
            }
        }

    }

    /**
     * Stop progresse observing
     */
    public function stopObservingProgress()
    {
        set_error_handler(
            function () {
                return false;
            }
        );
        register_shutdown_function(
            function () {
                return false;
            }
        );
    }

    /**
     * @param string $file
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */

    public function getFlagFile($file = "progress")
    {
        $flagFile = $this->getAbsoluteRootDir() . $this->tempDirectory . $this->filePrefix . $file . ".flag";

        if (!file_exists($flagFile)) {
            $this->_ioWrite->create($this->tempDirectory); // create path if not exists
            $io = $this->_ioRead->openFile($flagFile, "w+");
            $this->_ioRead->writeFile($flagFile, self::PENDING . ";;0");
            $io->close(); // close
        }
        return $flagFile;
    }

    /**
     * @param string $file
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getStats($file = "progress")
    {
        $this->_flagFile = $this->getFlagFile($file);
        $io = $this->_ioWrite->openFile($this->_flagFile, "r");
        $io->close(); // close
        return $io->stat();

    }

    /**
     * @param string $file
     * @param bool $logEnabled
     * @param string $name
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function readFlag($file = "progress")
    {

        $this->_flagFile = $this->getFlagFile($file);
        $io = $this->_ioWrite->openFile($this->_flagFile, "r");
        $line = $io->readCsv(0, ";");

        $status = null;
        if (isset($line[0])) {
            $status = $line[0];
        }
        $message = null;
        if (isset($line[1])) {
            $message = $line[1];
        }
        $percent = 0;
        if (isset($line[2])) {
            $percent = $line[2];
        }
        $io->close(); // close

        return array("status" => $status, "message" => $message, "percent" => $percent);

    }


    /** Update the flag file
     * @param $content
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function flagUpdate($content)
    {
        if (self::DEBUG) {
            return;
        }
        $io = $this->_ioRead->openFile($this->_flagFile, "w+");
        $this->_ioRead->writeFile($this->_flagFile, $content);
        $io->close(); // close

        if (php_sapi_name() === 'cli') {
            $time = $this->dateTime->date('Y-m-d H:i:s');
            $exploded = explode(";", $content);
            $status = $exploded[0];
            $message = $exploded[1];

            $percent = floor($exploded[2] / 2);
            if ($status == self::SUCCEEDED) {
                $status = "\e[92m" . $status . "\e[39m";
                fwrite(STDOUT, sprintf("\r %s %-20s [%-" . $percent . "s>%-" . (50 - $percent) . "s] %s%% : %-100s", $time, $status, str_pad("", $percent, "~"), str_pad("", 50 - $percent, " "), $percent * 2, $message));
            } elseif ($status == self::FAILED) {
                $status = "\e[91m\033[1m" . $status . "\033[0m\e[39m";
                fwrite(STDOUT, sprintf("\r %s %-20s : %-100s", $time, $status, $message));
            } elseif ($status == self::ERROR) {
                $status = "\e[91m\033[1m" . $status . "\033[0m\e[39m";
                fwrite(STDOUT, sprintf("\r %s %-20s : %-100s", $time, $status, $message));
            } elseif ($status == self::PROCESSING) {
                $status = "\e[93m\033[1m" . $status . "\033[0m\e[39m";
                fwrite(STDOUT, sprintf("\r %s %-20s [%-" . $percent . "s>%-" . (50 - $percent) . "s] %s%% : %-100s", $time, $status, str_pad("", $percent, "~"), str_pad("", 50 - $percent, " "), $percent * 2, $message));
            } else {
                $status = "\e[93m\033[1m" . self::PROCESSING . "\033[0m\e[39m";
                $message = str_replace(">", "", trim($content));
                fwrite(STDOUT, sprintf("\r %s %-20s : %-100s", $time, $status, $message));
            }
            fflush(STDOUT);
        }
    }

    /** catch the shutdown
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function shutdown()
    {

        if ($error = error_get_last()) {
            if (stripos($error['message'],"mcrypt") === false) {
                $this->logOnFail($error["message"]);
            }
        }
        $this->stopObservingProgress();
    }

    /** Add log on failure
     * @param $message
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public
    function logOnFail(
        $message
    )
    {
        $this->log($message, true, "FAILED");
    }


    /** Write the log file
     * @param $message
     * @param bool $updateFlag
     * @param string $status
     * @param int $percentage
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public
    function log(
        $message,
        $updateFlag = true,
        $status = "PROCESSING",
        $percentage = 0
    )
    {
        if ($this->_logEnabled) {
            $this->logger->notice($message);

        }
        if ($updateFlag) {
            $this->flagUpdate($status . ";" . $message . ";" . $percentage);
        }
    }


}