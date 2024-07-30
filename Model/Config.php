<?php

namespace WB\InvoiceEmail\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const XML_PATH_ENABLE = 'wb_invoiceemail/general/enable';

    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}
