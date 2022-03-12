<?php
class ModelExtensionPaymentCardExpresspay extends Model {
    const NAME_PAYMENT_METHOD                       = 'payment_card_expresspay_name_payment_method';
    const SORT_ORDER_PARAM_NAME                     = 'payment_card_expresspay_sort_order';
    const CURRENCY = 933;
    const RETURN_TYPE = 'redirect';
    
    private static $model;

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function getMethod($address, $total) 
    {
        $this->load->language('extension/payment/card_expresspay');
        $status = false;

        if ($total > 0) {
            $status = true;
        }

        $method_data = array();

        $code = 'card_expresspay';
        
        // Название метода оплаты
        $textTitle = $this->language->get('heading_title');
        if($this->config->get(self::NAME_PAYMENT_METHOD) !== null){
            $textTitle = $this->config->get(self::NAME_PAYMENT_METHOD);
        }
        
        $sortOrder = $this->config->get(self::SORT_ORDER_PARAM_NAME);

        if ($status) {
            $method_data = array(
                'code'       => $code,
                'title'      => $textTitle,
                'terms'      => '',
                'sort_order' => $sortOrder
            );
        }

        return $method_data;
    }

    public function setParams($data, $config)
    {
        self::$model = new CardExpressPayModel($config);
        $orderId = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($orderId);
        $amount = str_replace('.', ',', $this->currency->format($order_info['total'], $this->session->data['currency'], '', false));
        if ($this->session->data['currency'] !== "BYN") {
            $response = $this->getCurrencyRateFromNBRB($this->session->data['currency']);
            $amount = str_replace('.', ',', round($amount * $response->Cur_OfficialRate, 2));
        }

        $signatureParams['Token'] = self::$model->getToken();
        $signatureParams['ServiceId'] = self::$model->getServiceId();
        $signatureParams['AccountNo'] = $orderId;
        $signatureParams['Amount'] = $amount;
        $signatureParams['Currency'] = self::CURRENCY;
        $signatureParams['Info'] = str_replace('##order_id##', $orderId, self::$model->getInfo());
        $signatureParams['ReturnType'] = self::RETURN_TYPE;
        $signatureParams['ReturnUrl'] = $this->url->link('extension/payment/card_expresspay/success');
        $signatureParams['FailUrl'] = $this->url->link('extension/payment/card_expresspay/fail');
        $signatureParams["ReturnInvoiceUrl"] = "1";

        $data['Signature'] = self::computeSignature($signatureParams, self::$model->getSecretWord(), 'add-webcard-invoice');
        unset($signatureParams['Token']);
        $data = array_merge($data, $signatureParams);

        $data['Action'] = self::$model->getActionUrl();

        return $data;
    }
    
    private function getCurrencyRateFromNBRB($currency)
    {
        return json_decode(file_get_contents("https://www.nbrb.by/api/exrates/rates/$currency?parammode=2"));
    }
    /**
     * 
     * Формирование цифровой подписи
     * 
     * @param array  $signatureParams Список передаваемых параметров
     * @param string $secretWord      Секретное слово
     * @param string $method          Метод формирования цифровой подписи
     * 
     * @return string $hash           Сформированная цифровая подпись
     * 
     */
    private static function computeSignature($signatureParams, $secretWord, $method)
    {
        $normalizedParams = array_change_key_case($signatureParams, CASE_LOWER);
        $mapping = array(
            "get-qr-code"          => array(
                "token",
                "invoiceid",
                "viewtype",
                "imagewidth",
                "imageheight"
            ),
            "add-web-invoice"      => array(
                "token",
                "serviceid",
                "accountno",
                "amount",
                "currency",
                "expiration",
                "info",
                "surname",
                "firstname",
                "patronymic",
                "city",
                "street",
                "house",
                "building",
                "apartment",
                "isnameeditable",
                "isaddresseditable",
                "isamounteditable",
                "emailnotification",
                "smsphone",
                "returntype",
                "returnurl",
                "failurl",
                "returninvoiceurl"
            ),
            "add-webcard-invoice" => array(
                "token",
                "serviceid",
                "accountno",
                "expiration",
                "amount",
                "currency",
                "info",
                "returnurl",
                "failurl",
                "language",
                "sessiontimeoutsecs",
                "expirationdate",
                "returntype",
                "returninvoiceurl"
            )
        );
        $apiMethod = $mapping[$method];
        $result = "";
        foreach ($apiMethod as $item) {
            $result .= (isset($normalizedParams[$item])) ? $normalizedParams[$item] : '';
        }
        $hash = strtoupper(hash_hmac('sha1', $result, $secretWord));
        return $hash;
    }
}

class CardExpressPayModel
{
    const TOKEN_PARAM_NAME                          = 'payment_card_expresspay_token';
    const SERVICE_ID_PARAM_NAME                     = 'payment_card_expresspay_service_id';
    const SECRET_WORD_PARAM_NAME                    = 'payment_card_expresspay_secret_word';
    const IS_TEST_MODE_PARAM_NAME                   = 'payment_card_expresspay_is_test_mode';
    const API_URL_PARAM_NAME                        = 'payment_card_expresspay_api_url';
    const SANDBOX_URL_PARAM_NAME                    = 'payment_card_expresspay_sandbox_url';
    const INFO_PARAM_NAME                           = 'payment_card_expresspay_info';

    private $token;
    private $serviceId;
    private $secretWord;
    private $isTestMode;
    private $apiUrl;
    private $sandboxUrl;
    private $info;

    public function __construct($config)
    {
        if ($config == null)
            return;

        $this->setToken($config->get(self::TOKEN_PARAM_NAME));
        $this->setServiceId($config->get(self::SERVICE_ID_PARAM_NAME));
        $this->setSecretWord($config->get(self::SECRET_WORD_PARAM_NAME));
        $this->setIsTestMode($config->get(self::IS_TEST_MODE_PARAM_NAME));
        $this->setApiUrl($config->get(self::API_URL_PARAM_NAME));
        $this->setSandboxUrl($config->get(self::SANDBOX_URL_PARAM_NAME));
        $this->setInfo($config->get(self::INFO_PARAM_NAME));
    }

    public function getActionUrl()
    {
        if ($this->isTestMode) {
            return $this->sandboxUrl.'/web_cardinvoices';
        } else {
            return $this->apiUrl.'/web_cardinvoices';
        }
    }

    public function getToken()
    {
        return $this->token;
    }

    private function setToken($token)
    {
        $this->token = $token;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    private function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
    }

    public function getSecretWord()
    {
        return $this->secretWord;
    }

    private function setSecretWord($secretWord)
    {
        $this->secretWord = $secretWord;
    }

    private function setIsTestMode($isTestMode)
    {
        $checkboxValue = $this->normCheckboxValue($isTestMode);
        $this->isTestMode = $checkboxValue;
    }

    private function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    private function setSandboxUrl($sandboxUrl)
    {
        $this->sandboxUrl = $sandboxUrl;
    }
    
    public function getInfo()
    {
        return $this->info;
    }

    private function setInfo($info)
    {
        $this->info = $info;
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