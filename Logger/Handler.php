<?php

namespace WB\InvoiceEmail\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    protected $fileName = '/var/log/cod_order_invoice.log';
    protected $loggerType = Logger::INFO;
}