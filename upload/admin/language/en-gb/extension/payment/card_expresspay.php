<?php
// Heading
$_['heading_title']         = 'Express pay: Bank cards';

// Text
$_['text_extension']        = 'Extensions';
$_['text_success']          = 'You have successfully changed the module settings';
$_['text_card_expresspay']  = '<a target="_blank" href="https://express-pay.by/extensions/opencart-3-x/acquiring">
                            <img src="view/image/payment/card_expresspay.png" alt="Express-pay Website" title="Express-pay Website"/></a>';
$_['text_edit']             = 'Change Settings';

// Setting field
$_['namePaymentMethodLabel']            = 'Payment method name';
$_['namePaymentMethodTooltip']          = 'Name displayed when selecting high pay';
$_['namePaymentMethodDefault']          = 'Express pay: Bank card';
$_['tokenLabel']                        = 'Token';
$_['tokenTooltip']                      = 'API key of the service provider';
$_['serviceIdLabel']                    = 'Service number';
$_['serviceIdTooltip']                  = 'Service number in the system express-pay.by';
$_['secretWordLabel']                   = 'Secret word for invoices';
$_['secretWordTooltip']                 = 'Secret word to form a digital signature for invoices';
$_['secretWordNotificationLabel']       = 'Secret word for notifications';
$_['secretWordNotificationTooltip']     = 'Secret word to form a digital signature for notifications';
$_['useSignatureForNotificationLabel']  = 'Use a digital signature for notifications';
$_['useTestModeLabel']                  = 'Use test mode';
$_['urlApiLabel']                       = 'API Address';
$_['urlApiTooltip']                     = 'Address to work with the API';
$_['urlSandboxLabel']                   = 'Sandbox API Address';
$_['urlSandboxTooltip']                 = 'Address to work with the sandbox API';
$_['infoLabel']                         = 'Order description';
$_['infoTooltip']                       = 'The order description will be displayed when paying to the client';
$_['infoDefault']                       = 'Order number ##order_id## in the store '. $_SERVER['HTTP_HOST'];
$_['urlForNotificationLabel']           = 'Address for receiving notifications';
$_['urlForNotificationTooltip']         = 'The address for receiving notifications about the status of the order to the site is set in the personal account';
$_['messageSuccessLabel']               = 'Message on successful invoice creation';
$_['messageSuccessTooltip']             = 'Message text on successful invoice creation for the client';
$_['messageSuccessDefault']             = 'Your order number: ##order_id##';
$_['entryStatus']                       = 'Status';
$_['entrySortOrder']                    = 'Sort order';

$_['processedOrderStatusLabel']     = 'New order status';
$_['processedOrderStatusTooltip']   = 'Set the status of the order received for processing';
$_['failOrderStatusLabel']          = 'Order status on error';
$_['failOrderStatusTooltip']        = 'Order status to be set when an error occurs';
$_['successOrderStatusLabel']       = 'Paid order status';
$_['successOrderStatusTooltip']     = 'Set the status of the order for which payment has been received';

// Error
$_['errorPermission']           = 'Warning: You have no rights to change the payment module settings!';
$_['errorNamePaymentMethod']    = 'The name of the payment method is mandatory';
$_['errorToken']                = 'The token is mandatory';
$_['errorServiceId']            = 'The service number is mandatory';
$_['errorAPIUrl']               = 'The API address is mandatory';
$_['errorSandboxUrl']           = 'The address of the test API is mandatory';