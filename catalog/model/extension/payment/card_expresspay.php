<?php
class ModelExtensionPaymentCardExpresspay extends Model {

    private static $model;

    const CURRENCY = 933;
    const RETURN_TYPE = 'redirect';

    public function __construct($registry)
    {
        parent::__construct($registry);

        //self::$model = new TestExpressPayModel();
    }

    public function getMethod($address, $total) {

        $this->load->language('extension/payment/card_expresspay');

        $status = false;

        if($total > 0)
        {
            $status = true;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'       => 'card_expresspay',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get('payment_card_expresspay_sort_order')
            );
        }

        return $method_data;
    }

    public function setParams($data, $config)
    {

        self::$model = new CardExpressPayModel($config);

        $data['ServiceId'] = self::$model->getServiceId();
        $data['Currency'] = self::CURRENCY;
        $data['Info'] = str_replace('##order_id##',$data['AccountNo'],self::$model->getInfo());
        $data['ReturnType'] = self::RETURN_TYPE;
        $data['ReturnUrl'] = $this->url->link('extension/payment/card_expresspay/success');
        $data['FailUrl'] = $this->url->link('extension/payment/card_expresspay/fail');

        $data['Signature'] = $this->compute_signature_add_invoice($data, self::$model->getSecretWord(), self::$model->getToken());

        $data['Action'] = self::$model->getActionUrl();

        return $data;
    }

    public function checkResponse($signature, $request_params, $config)
    {

        self::$model = new CardExpressPayModel($config);

        $token = self::$model->getToken();

        $compute_signature = $this->compute_signature_success_invoice($request_params,$token);

        var_dump($compute_signature);

        var_dump($request_params);

        exit(1);

        return $signature == $compute_signature;
    }


    private function compute_signature_success_invoice($request_params, $token)
    {
        //$normalized_params = array_change_key_case($request_params, CASE_LOWER);
        $api_method = array(
            'ExpressPayAccountNumber',
            'ExpressPayInvoiceNo'
        );

        $result = $token;

        foreach ($api_method as $item)
            $result .= $request_params[$item];

        $hash = strtoupper(hash_hmac('sha1', $result, ''));

        return $hash;
    }

    private function compute_signature_add_invoice($request_params, $secret_word, $token) {
        $secret_word = trim($secret_word);
        $normalized_params = array_change_key_case($request_params, CASE_LOWER);
        $api_method = array(
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
            "returntype"
        );

        $result = $token;

        foreach ($api_method as $item)
            $result .= ( isset($normalized_params[$item]) ) ? $normalized_params[$item] : '';

        $hash = strtoupper(hash_hmac('sha1', $result, $secret_word));

        return $hash;
    }

}

class CardExpressPayModel
{

    const URL_API_LINK = 'https://api.express-pay.by/v1/web_cardinvoices';
    const URL_SANDBOX_API_LINK = 'https://sandbox-api.express-pay.by/v1/web_cardinvoices';

    const TOKEN_PARAM_NAME                                  = 'payment_card_expresspay_token';
    const SERVICE_ID_PARAM_NAME                             = 'payment_card_expresspay_service_id';
    const SECRET_WORD_PARAM_NAME                            = 'payment_card_expresspay_secret_word';
    const USE_SIGNATURE_FOR_NOTIFICATION_PARAM_NAME         = 'payment_card_expresspay_is_use_signature_for_notification';
    const SECRET_WORD_NOTIFICATION_PARAM_NAME               = 'payment_card_expresspay_secret_word_for_notification';
    const INFO_PARAM_NAME                                   = 'payment_card_expresspay_info';

    private $token;
    private $serviceId;
    private $secretWord;
    private $secretWordNotification;
    private $useSignatureNotification;
    private $info;
    private $isNameEdit;
    private $isAmountEdit;
    private $isAddressEdit;
    private $test_token_list = array('a75b74cbcfe446509e8ee874f421bd64'
                                    ,'a75b74cbcfe446509e8ee874f421bd64'
                                    ,'a75b74cbcfe446509e8ee874f421bd66'
                                    ,'a75b74cbcfe446509e8ee874f421bd67'
                                    ,'a75b74cbcfe446509e8ee874f421bd68');

    public function __construct($config)
    {
        $this->token = $config->get(self::TOKEN_PARAM_NAME);
        $this->serviceId = $config->get(self::SERVICE_ID_PARAM_NAME);
        $this->secretWord = $config->get(self::SECRET_WORD_PARAM_NAME);
        $this->secretWordNotification = $config->get(self::USE_SIGNATURE_FOR_NOTIFICATION_PARAM_NAME);
        $this->useSignatureNotification = $config->get(self::SECRET_WORD_NOTIFICATION_PARAM_NAME);
        $this->info = $config->get(self::INFO_PARAM_NAME);
    }

    public function getActionUrl()
    {

        foreach($this->test_token_list as &$t)
        {
            if($this->token == $t)
                return self::URL_SANDBOX_API_LINK;
        }
        return self::URL_API_LINK;
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

    public function getSecretWordNotification()
    {
        return $this->secretWordNotification;
    }

    public function setSecretWordNotification($secretWordNotification)
    {
        $this->secretWordNotification = $secretWordNotification;
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

    public function getInfo()
    {
        return $this->info;
    }

    public function setInfo($info)
    {
        $this->info = $info;
    }

}

