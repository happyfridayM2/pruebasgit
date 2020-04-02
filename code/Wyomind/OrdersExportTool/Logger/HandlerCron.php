<?php

namespace Wyomind\OrdersExportTool\Logger;

class HandlerCron extends \Magento\Framework\Logger\Handler\Base
{
    protected $fileName = '/var/log/OrdersExportTool-cron.log';
    protected $loggerType = \Monolog\Logger::NOTICE;
}
