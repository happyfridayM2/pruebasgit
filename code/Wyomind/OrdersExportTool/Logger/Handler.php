<?php

namespace Wyomind\OrdersExportTool\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{

    protected $fileName = '/var/log/OrdersExportTool.log';
    protected $loggerType = \Monolog\Logger::NOTICE;
}
