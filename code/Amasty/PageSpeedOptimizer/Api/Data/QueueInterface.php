<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Api\Data;

interface QueueInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const QUEUE_ID = 'queue_id';
    const FILENAME = 'filename';
    const EXTENSION = 'extension';
    const TOOL = 'tool';
    const RESOLUTIONS = 'resolutions';
    const WEBP = 'webp';
    const DUMP_ORIGINAL = 'dump_original';
    const RESIZE_ALGORITHM = 'resize_algorithm';
    /**#@-*/

    /**
     * @return int
     */
    public function getQueueId();

    /**
     * @param int $queueId
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface
     */
    public function setQueueId($queueId);

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @param string $filename
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface
     */
    public function setFilename($filename);

    /**
     * @return string
     */
    public function getExtension();

    /**
     * @param string $extension
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface
     */
    public function setExtension($extension);

    /**
     * @return bool
     */
    public function isUseWebP();

    /**
     * @param bool $isUseWebP
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface
     */
    public function setIsUseWebP($isUseWebP);

    /**
     * @return array
     */
    public function getResolutions();

    /**
     * @param array $resolutions
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface
     */
    public function setResolutions($resolutions);

    /**
     * @return int
     */
    public function getTool();

    /**
     * @param string $tool
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface
     */
    public function setTool($tool);

    /**
     * @return int
     */
    public function getResizeAlgorithm();

    /**
     * @param int $resizeAlgorithm
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface
     */
    public function setResizeAlgorithm($resizeAlgorithm);

    /**
     * @return int
     */
    public function isDumpOriginal();

    /**
     * @param bool $isDumpOriginal
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\QueueInterface
     */
    public function setIsDumpOriginal($isDumpOriginal);
}
