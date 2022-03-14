<?php

class ModelExtensionPaymentCardExpresspay extends Model
{
    const NOTIFICATION_URL                          = 'index.php?route=extension/payment/card_expresspay_api/notify';
    const NAME_PAYMENT_METHOD                       = 'payment_card_expresspay_name_payment_method';
    const TOKEN_PARAM_NAME                          = 'payment_card_expresspay_token';
    const SERVICE_ID_PARAM_NAME                     = 'payment_card_expresspay_service_id';
    const SECRET_WORD_PARAM_NAME                    = 'payment_card_expresspay_secret_word';
    const USE_SIGNATURE_FOR_NOTIFICATION_PARAM_NAME = 'payment_card_expresspay_is_use_signature_for_notification';
    const SECRET_WORD_NOTIFICATION_PARAM_NAME       = 'payment_card_expresspay_secret_word_for_notification';
    const NOTIFICATION_URL_PARAM_NAME               = 'payment_card_expresspay_url_notification';
    const IS_TEST_MODE_PARAM_NAME                   = 'payment_card_expresspay_is_test_mode';
    const API_URL_PARAM_NAME                        = 'payment_card_expresspay_api_url';
    const SANDBOX_URL_PARAM_NAME                    = 'payment_card_expresspay_sandbox_url';
    const INFO_PARAM_NAME                           = 'payment_card_expresspay_info';
    const MESSAGE_SUCCESS_PARAM_NAME                = 'payment_card_expresspay_message_success';
    const STATUS_PARAM_NAME                         = 'payment_card_expresspay_status';
    const SORT_ORDER_PARAM_NAME                     = 'payment_card_expresspay_sort_order';
    const PROCESSED_STATUS_ID_PARAM_NAME            = 'payment_card_expresspay_processed_status_id';
    const SUCCESS_STATUS_ID_PARAM_NAME              = 'payment_card_expresspay_success_status_id';
    const FAIL_STATUS_ID_PARAM_NAME                 = 'payment_card_expresspay_fail_status_id';


    private static $model;

    public function __construct($registry)
    {
        parent::__construct($registry);

        self::$model = new CardExpressPayModel();
    }

    public function setParametersFromConfig($config, $request, $data)
    {
        // Название метода оплаты
        if (isset($request[self::NAME_PAYMENT_METHOD])) {
            self::$model->setName($request[self::NAME_PAYMENT_METHOD]);
        } else if($config->get(self::NAME_PAYMENT_METHOD) !== null){
            self::$model->setName($config->get(self::NAME_PAYMENT_METHOD));
        } else {
            self::$model->setName($this->language->get('namePaymentMethodDefault'));
        }

        // ТОКЕН
        if (isset($request[self::TOKEN_PARAM_NAME])) {
            self::$model->setToken($request[self::TOKEN_PARAM_NAME]);
        } else {
            self::$model->setToken($config->get(self::TOKEN_PARAM_NAME));
        }

        // Номер услуги
        if (isset($request[self::SERVICE_ID_PARAM_NAME])) {
            self::$model->setServiceId($request[self::SERVICE_ID_PARAM_NAME]);
        } else {
            self::$model->setServiceId($config->get(self::SERVICE_ID_PARAM_NAME));
        }

        // Секретное слово
        if (isset($request[self::SECRET_WORD_PARAM_NAME])) {
            self::$model->setSecretWord($request[self::SECRET_WORD_PARAM_NAME]);
        } else {
            self::$model->setSecretWord($config->get(self::SECRET_WORD_PARAM_NAME));
        }

        // Использовать цифровую подпись для уведомлений
        if (isset($request[self::USE_SIGNATURE_FOR_NOTIFICATION_PARAM_NAME])) {
            self::$model->setUseSignatureNotification($request[self::USE_SIGNATURE_FOR_NOTIFICATION_PARAM_NAME]);
        } else {
            self::$model->setUseSignatureNotification($config->get(self::USE_SIGNATURE_FOR_NOTIFICATION_PARAM_NAME));
        }

        // Секретное слово для уведомлений
        if (isset($request[self::SECRET_WORD_NOTIFICATION_PARAM_NAME])) {
            self::$model->setSecretWordNotification($request[self::SECRET_WORD_NOTIFICATION_PARAM_NAME]);
        } else {
            self::$model->setSecretWordNotification($config->get(self::SECRET_WORD_NOTIFICATION_PARAM_NAME));
        }

        // Использовать тестовый режим
        if (isset($request[self::IS_TEST_MODE_PARAM_NAME])) {
            self::$model->setIsTestMode($request[self::IS_TEST_MODE_PARAM_NAME]);
        } else {
            self::$model->setIsTestMode($config->get(self::IS_TEST_MODE_PARAM_NAME));
        }

        // Адрес API
        if (isset($request[self::API_URL_PARAM_NAME])) {
            self::$model->setApiUrl($request[self::API_URL_PARAM_NAME]);
        } else if($config->get(self::API_URL_PARAM_NAME) !== null){
            self::$model->setApiUrl($config->get(self::API_URL_PARAM_NAME));
        } else {
            self::$model->setApiUrl('https://api.express-pay.by/v1/');
        }

        // Адрес тестового API 
        if (isset($request[self::SANDBOX_URL_PARAM_NAME])) {
            self::$model->setSandboxUrl($request[self::SANDBOX_URL_PARAM_NAME]);
        } else if($config->get(self::SANDBOX_URL_PARAM_NAME) !== null){
            self::$model->setSandboxUrl($config->get(self::SANDBOX_URL_PARAM_NAME));
        } else {
            self::$model->setSandboxUrl('https://sandbox-api.express-pay.by/v1/');
        }

        // Информация о платеже
        if (isset($request[self::INFO_PARAM_NAME])) {
            self::$model->setInfo($request[self::INFO_PARAM_NAME]);
        } else if($config->get(self::INFO_PARAM_NAME) !== null){
            self::$model->setInfo($config->get(self::INFO_PARAM_NAME));
        } else {
            self::$model->setInfo($this->language->get('infoDefault'));
        }

        // Сообщение при успешном создании счёта
        if (isset($request[self::MESSAGE_SUCCESS_PARAM_NAME])) {
            self::$model->setMessageSuccess($request[self::MESSAGE_SUCCESS_PARAM_NAME]);
        } else if($config->get(self::MESSAGE_SUCCESS_PARAM_NAME) !== null){
            self::$model->setMessageSuccess($config->get(self::MESSAGE_SUCCESS_PARAM_NAME));
        } else {
            self::$model->setMessageSuccess($this->language->get('messageSuccessDefault'));
        }

        // Статус
        if (isset($request[self::STATUS_PARAM_NAME])) {
            self::$model->setStatus($request[self::STATUS_PARAM_NAME]);
        } else {
            self::$model->setStatus($config->get(self::STATUS_PARAM_NAME));
        }

        // Порядок сортировки
        if (isset($request[self::SORT_ORDER_PARAM_NAME])) {
            self::$model->setSortOrder($request[self::SORT_ORDER_PARAM_NAME]);
        } else {
            self::$model->setSortOrder($config->get(self::SORT_ORDER_PARAM_NAME));
        }

        // Статус нового заказа
        if (isset($request[self::PROCESSED_STATUS_ID_PARAM_NAME])) {
            self::$model->setProcessedStatus($request[self::PROCESSED_STATUS_ID_PARAM_NAME]);
        } else {
            self::$model->setProcessedStatus($config->get(self::PROCESSED_STATUS_ID_PARAM_NAME));
        }

        // Статус оплаченого заказа
        if (isset($request[self::SUCCESS_STATUS_ID_PARAM_NAME])) {
            self::$model->setSuccessStatus($request[self::SUCCESS_STATUS_ID_PARAM_NAME]);
        } else {
            self::$model->setSuccessStatus($config->get(self::SUCCESS_STATUS_ID_PARAM_NAME));
        }

        // Статус ошибки при заказе
        if (isset($request[self::FAIL_STATUS_ID_PARAM_NAME])) {
            self::$model->setFailStatus($request[self::FAIL_STATUS_ID_PARAM_NAME]);
        } else {
            self::$model->setFailStatus($config->get(self::FAIL_STATUS_ID_PARAM_NAME));
        }

        return $this->exportParametersInArray($data);
    }

    private function exportParametersInArray($data)
    {
        $data[self::NAME_PAYMENT_METHOD]                        = self::$model->getName();
        $data[self::TOKEN_PARAM_NAME]                           = self::$model->getToken();
        $data[self::SERVICE_ID_PARAM_NAME]                      = self::$model->getServiceId();
        $data[self::SECRET_WORD_PARAM_NAME]                     = self::$model->getSecretWord();
        $data[self::USE_SIGNATURE_FOR_NOTIFICATION_PARAM_NAME]  = self::$model->getUseSignatureNotification();
        $data[self::SECRET_WORD_NOTIFICATION_PARAM_NAME]        = self::$model->getSecretWordNotification();
        $data[self::NOTIFICATION_URL_PARAM_NAME]                = HTTPS_CATALOG . self::NOTIFICATION_URL;
        $data[self::IS_TEST_MODE_PARAM_NAME]                    = self::$model->getIsTestMode();
        $data[self::API_URL_PARAM_NAME]                         = self::$model->getApiUrl();
        $data[self::SANDBOX_URL_PARAM_NAME]                     = self::$model->getSandboxUrl();
        $data[self::INFO_PARAM_NAME]                            = self::$model->getInfo();
        $data[self::MESSAGE_SUCCESS_PARAM_NAME]                 = self::$model->getMessageSuccess();
        $data[self::STATUS_PARAM_NAME]                          = self::$model->getStatus();
        $data[self::SORT_ORDER_PARAM_NAME]                      = self::$model->getSortOrder();
        $data[self::PROCESSED_STATUS_ID_PARAM_NAME]             = self::$model->getProcessedStatus();
        $data[self::SUCCESS_STATUS_ID_PARAM_NAME]               = self::$model->getSuccessStatus();
        $data[self::FAIL_STATUS_ID_PARAM_NAME]                  = self::$model->getFailStatus();

        return $data;
    }
}

class CardExpressPayModel
{
    private $name;
    private $token;
    private $serviceId;
    private $secretWord;
    private $useSignatureNotification;
    private $secretWordNotification;
    private $isShowQrCode;
    private $isNameEdit;
    private $isAmountEdit;
    private $isAddressEdit;
    private $pathInErip;
    private $isTestMode;
    private $apiUrl;
    private $sandboxUrl;
    private $info;
    private $messageSuccess;
    private $status;
    private $sortOrder;
    private $processedStatus;
    private $successStatus;
    private $failStatus;

    public function __construct()
    {
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
    }

    public function getSecretWord()
    {
        return $this->secretWord;
    }

    public function setSecretWord($secretWord)
    {
        $this->secretWord = $secretWord;
    }

    public function getUseSignatureNotification()
    {
        return $this->useSignatureNotification;
    }

    public function setUseSignatureNotification($useSignatureNotification)
    {
        $checkboxValue = $this->normCheckboxValue($useSignatureNotification);
        $this->useSignatureNotification = $checkboxValue;
    }

    public function getSecretWordNotification()
    {
        return $this->secretWordNotification;
    }

    public function setSecretWordNotification($secretWordNotification)
    {
        $this->secretWordNotification = $secretWordNotification;
    }

    public function getIsTestMode()
    {
        return $this->isTestMode;
    }

    public function setIsTestMode($isTestMode)
    {
        $checkboxValue = $this->normCheckboxValue($isTestMode);
        $this->isTestMode = $checkboxValue;
    }

    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function getSandboxUrl()
    {
        return $this->sandboxUrl;
    }

    public function setSandboxUrl($sandboxUrl)
    {
        $this->sandboxUrl = $sandboxUrl;
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }

    public function getMessageSuccess()
    {
        return $this->messageSuccess;
    }

    public function setMessageSuccess($messageSuccess)
    {
        $this->messageSuccess = $messageSuccess;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    public function getProcessedStatus()
    {
        return $this->processedStatus;
    }

    public function setProcessedStatus($processedStatus)
    {
        $this->processedStatus = $processedStatus;
    }

    public function getSuccessStatus()
    {
        return $this->successStatus;
    }

    public function setSuccessStatus($successStatus)
    {
        $this->successStatus = $successStatus;
    }

    public function getFailStatus()
    {
        return $this->failStatus;
    }

    public function setFailStatus($failStatus)
    {
        $this->failStatus = $failStatus;
    }

    private function normCheckboxValue($checkboxValue)
    {
        $normValue = 0;

        if ($checkboxValue == null) {
            return $normValue;
        }

        switch ($checkboxValue) {
            case "on":
            case 1:
            case "1":
                $normValue = 1;
        }

        return $normValue;
    }
}