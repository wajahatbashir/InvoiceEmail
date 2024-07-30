# WB Invoice Email Module

## Overview
The WB Invoice Email Module is a custom Magento 2 extension that automates the creation of invoices and sends order confirmation emails upon order placement for specified payment methods.

## Features
- Automatically creates invoices for orders placed with selected payment methods.
- Sends order confirmation emails with the invoice attached.
- Configurable payment methods via the admin panel.

## Installation
1. Download and extract the module to `app/code/WB/InvoiceEmail`.
2. Run the following commands in the Magento root directory:
    ```bash
    php bin/magento setup:upgrade
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy
    php bin/magento cache:flush
    ```

## Configuration
1. Go to **Stores > Configuration > JCO Custom Modules > Invoice Email Settings**.
2. Enable the module.
3. Select the payment methods for which invoices should be automatically created.

## Usage
After configuration, whenever an order is placed using the selected payment methods, the module will automatically create an invoice and send an order confirmation email with the invoice attached.

## Developer Information
- **Namespace**: `WB`
- **Module**: `InvoiceEmail`
- **Observer**: `WB\InvoiceEmail\Observer\Sales\Order\CodOrderAutoInvoice`
- **Plugin**: `WB\InvoiceEmail\Plugin\Order\PlaceAfterPlugin`


## Support

For any issues, please contact the module developer.


## License
This module is licensed under the MIT License. See the LICENSE file for more details.