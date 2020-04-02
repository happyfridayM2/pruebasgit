<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Queue;

use Amasty\PageSpeedOptimizer\Api\Data\QueueInterface;
use Magento\Framework\Model\AbstractModel;

class Queue extends AbstractModel implements QueueInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\PageSpeedOptimizer\Model\Queue\ResourceModel\Queue::class);
        $this->setIdFieldName(QueueInterface::QUEUE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getQueueId()
    {
        return (int)$this->_getData(QueueInterface::QUEUE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQueueId($queueId)
    {
        return $this->setData(QueueInterface::QUEUE_ID, (int)$queueId);
    }

    /**
     * @inheritdoc
     */
    public function getFilename()
    {
        return $this->_getData(QueueInterface::FILENAME);
    }

    /**
     * @inheritdoc
     */
    public function setFilename($filename)
    {
        return $this->setData(QueueInterface::FILENAME, $filename);
    }

    /**
     * @inheritdoc
     */
    public function getExtension()
    {
        return $this->_getData(QueueInterface::EXTENSION);
    }

    /**
     * @inheritdoc
     */
    public function setExtension($extension)
    {
        return $this->setData(QueueInterface::EXTENSION, $extension);
    }

    /**
     * @inheritdoc
     */
    public function isUseWebP()
    {
        return (bool)$this->_getData(QueueInterface::WEBP);
    }

    /**
     * @inheritdoc
     */
    public function setIsUseWebP($isUseWebP)
    {
        return $this->setData(QueueInterface::WEBP, (bool)$isUseWebP);
    }

    /**
     * @inheritdoc
     */
    public function getResolutions()
    {
        $data = $this->_getData(QueueInterface::RESOLUTIONS);
        if (empty($data)) {
            return [];
        }

        return explode(',', $data);
    }

    /**
     * @inheritdoc
     */
    public function setResolutions($resolutions)
    {
        if (is_array($resolutions)) {
            $resolutions = implode(',', $resolutions);
        }

        return $this->setData(QueueInterface::RESOLUTIONS, $resolutions);
    }

    /**
     * @inheritDoc
     */
    public function getTool()
    {
        return (int)$this->_getData(QueueInterface::TOOL);
    }

    /**
     * @inheritDoc
     */
    public function setTool($tool)
    {
        return $this->setData(QueueInterface::TOOL, (int)$tool);
    }

    /**
     * @inheritdoc
     */
    public function isDumpOriginal()
    {
        return (bool)$this->_getData(QueueInterface::DUMP_ORIGINAL);
    }

    /**
     * @inheritdoc
     */
    public function setIsDumpOriginal($isDumpOriginal)
    {
        return $this->setData(QueueInterface::DUMP_ORIGINAL, (bool)$isDumpOriginal);
    }

    /**
     * @inheritdoc
     */
    public function getResizeAlgorithm()
    {
        return (int)$this->_getData(QueueInterface::RESIZE_ALGORITHM);
    }

    /**
     * @inheritdoc
     */
    public function setResizeAlgorithm($resizeAlgorithm)
    {
        return $this->setData(QueueInterface::RESIZE_ALGORITHM, (int)$resizeAlgorithm);
    }
}
