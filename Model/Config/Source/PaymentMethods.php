<?php

namespace WB\InvoiceEmail\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Config as PaymentConfig;

class PaymentMethods implements ArrayInterface
{
    protected $paymentConfig;

    public function __construct(PaymentConfig $paymentConfig)
    {
        $this->paymentConfig = $paymentConfig;
    }

    public function toOptionArray()
    {
        $methods = $this->paymentConfig->getActiveMethods();
        $options = [];

        foreach ($methods as $code => $method) {
            $options[] = [
                'value' => $code,
                'label' => $method->getTitle()
            ];
        }

        return $options;
    }
}
