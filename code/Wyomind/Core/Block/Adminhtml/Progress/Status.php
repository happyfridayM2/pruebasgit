<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Core\Block\Adminhtml\Progress;

/**
 * Class Status
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Wyomind\Core\Helper\Progress
     */
    private $helperProgress;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $coreDate;
    /**
     * Name of the column that stores the scheduled tasks settings
     * @var null
     */
    private $field;
    /**
     * @var string
     */
    private $module;

    /**
     * Status constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Backend\Block\Context $context
     * @param string $module
     * @param null $field
     * @param array $data
     */
    public function __construct(

        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Backend\Block\Context $context,
        $module="Core",
        $field=null,
        array $data=[]
    ) {

        parent::__construct($context, $data);

        $this->coreDate=$coreDate;

        $this->helperProgress=$objectManager->create("Wyomind\\" . $module . "\Helper\Progress");
        $this->field=$field;
        $this->module=$module;
        $this->objectManager=$objectManager;
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helperProgress=$this->helperProgress;
        try {

            $line=$helperProgress->readFlag($row->getId());

            $stats=$helperProgress->getStats($row->getId());
            if ($line["status"] == $helperProgress::SUCCEEDED) {
                $line["status"]=$this->checkCronTasks($line["status"], $row, $stats["mtime"]);
            }

            switch ($line["status"]) {
                case $helperProgress::SUCCEEDED:
                    $severity='notice';
                    $status=($line["status"]);
                    break;
                case $helperProgress::PENDING:
                    $severity='minor';
                    $status=($line["status"]);

                    break;
                case $helperProgress::PROCESSING:
                    $percent=round($line["percent"]);
                    $severity='minor';
                    $status=($line["status"]) . " [" . $percent . "%]";
                    break;
                case $helperProgress::HOLD:
                    $severity='major';
                    $status=($line["status"]);
                    break;
                case $helperProgress::FAILED:
                    $severity='critical';
                    $status=($line["status"]);
                    break;
                default :
                    $severity='critical';
                    $status=($helperProgress::ERROR);
                    break;
            }
        } catch (\Exception $exception) {
            $severity='minor';
            $line["message"]=$exception->getMessage();
            $status=($helperProgress::PENDING);
        }


        $tooltip=null;
        $tooltipClass=null;
        if (isset($line["message"]) && $line["message"] != "") {
            $tooltip="<div class=\"tooltip-content\">" . $line["message"] . "</div>";
            $tooltipClass="tooltip";
        }

        if ($status == $helperProgress::PENDING) {

            $tooltipClass="tooltip";
            $tooltip="<div class=\"tooltip-content\">" . __("Next Schedule") . " " . $this->getNextSchedule($row) . " </div > ";
        }
        $script="<script language='javascript' type='text/javascript'>var updater_url='" . $this->getUrl('wyomind/progress/updater') . "'</script>";
        return $script . "<span class='$tooltipClass grid-severity-$severity updater' data-module='" . $this->module . "' data-field='" . $this->field . "' data-cron='" . $row->getData($this->field) . "' data-id='" . $row->getId() . "' ><span > " . __($status) . "</span > " . $tooltip . "</span > ";
    }

    /**
     * @param $status
     * @param \Magento\Framework\DataObject $row
     * @param $mtime
     * @return mixed
     */

    protected function checkCronTasks($status, \Magento\Framework\DataObject $row, $mtime)
    {
        $helperProgress=$this->helperProgress;
        $cron=array();
        $cron['current']['localTime']=$this->coreDate->timestamp();
        $cron['file']['localTime']=$this->coreDate->timestamp($mtime);
        $cronExpr=json_decode($row->getData($this->field));
        $i=0;

        if (isset($cronExpr->days)) {
            foreach ($cronExpr->days as $day) {
                foreach ($cronExpr->hours as $hour) {
                    $time = explode(':', $hour);

                    if (date('l', $cron['current']['localTime']) == $day) {
                        $cron['tasks'][$i]['localTime'] = strtotime($this->coreDate->date('Y-m-d')) + ($time[0] * 60 * 60) + ($time[1] * 60);
                    } else {
                        $cron['tasks'][$i]['localTime'] = strtotime("last " . $day, $cron['current']['localTime']) + ($time[0] * 60 * 60) + ($time[1] * 60);
                    }
                    if ($cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['current']['localTime']) {
                        $status = $helperProgress::PENDING;

                        continue 2;
                    }
                }
            }
        }

        return $status;
    }

    protected function getNextSchedule($row)
    {
        $cron['current']['localTime']=$this->coreDate->timestamp();
        $cron['current']['gmtTime']=$this->coreDate->gmtTimestamp();
        $cronExpr=json_decode($row->getData($this->field));


        $schedules=[];

        if (isset($cronExpr->days)) {
            foreach ($cronExpr->days as $day) {
                foreach ($cronExpr->hours as $hour) {
                    $time = explode(':', $hour);
                    if (date('l', $cron['current']['localTime']) == $day) {
                        $schedule = strtotime($this->coreDate->date('Y-m-d')) + ($time[0] * 60 * 60) + ($time[1] * 60);


                    } else {
                        $schedule = strtotime("next " . $day, $cron['current']['localTime']) + ($time[0] * 60 * 60) + ($time[1] * 60);
                    }
                    if ($schedule > $cron['current']['localTime']) {
                        $schedules[] = $schedule;
                    }
                }
            }
        }
        sort($schedules);

        if (!empty($schedules)) {
            return $this->objectManager->create(
                'Magento\Framework\Stdlib\DateTime\TimezoneInterface'
            )->formatDate(
                date("Y-m-d H:i:s", $schedules[0]),
                \IntlDateFormatter::MEDIUM,
                true
            );
        } else {
            return __("not set");
        }

    }
}