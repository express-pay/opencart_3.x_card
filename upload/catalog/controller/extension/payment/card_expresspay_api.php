<?php

/**
 * @package       ExpressPay Payment Module for OpenCart
 * @author        ООО "ТриИнком" <info@express-pay.by>
 * @copyright     (c) 2022 Экспресс Платежи. Все права защищены.
 */

class ControllerExtensionPaymentCardExpresspayApi extends Controller{
    const USE_SIGNATURE_FOR_NOTIFICATION_PARAM_NAME         = 'payment_card_expresspay_is_use_signature_for_notification';
    const SECRET_WORD_NOTIFICATION_PARAM_NAME               = 'payment_card_expresspay_secret_word_for_notification';
    const PROCESSED_STATUS_ID_PARAM_NAME                    = 'payment_card_expresspay_processed_status_id';
    const SUCCESS_STATUS_ID_PARAM_NAME                      = 'payment_card_expresspay_success_status_id';
    const FAIL_STATUS_ID_PARAM_NAME                         = 'payment_card_expresspay_fail_status_id';

    public function notify()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->load->model('extension/payment/card_expresspay_log');
            $dataJSON = (isset($this->request->post['Data'])) ? htmlspecialchars_decode($this->request->post['Data']) : '';
            $signature = (isset($this->request->post['Signature'])) ? $this->request->post['Signature'] : '';

            $useSignatureForNotification = ($this->config->get(self::USE_SIGNATURE_FOR_NOTIFICATION_PARAM_NAME) == 'on') ? true : false;
            if ($useSignatureForNotification) {
                $secretWordForNotification = $this->config->get(self::SECRET_WORD_NOTIFICATION_PARAM_NAME);

                $valid_signature = self::computeSignature(array("data" => $dataJSON), $secretWordForNotification, 'notification');
                if ($valid_signature == $signature) {
                    self::notify_success($dataJSON);
                } else {                    
                    header('HTTP/1.1 403 FORBIDDEN');
                    echo 'FAILED | Access is denied';
                    $this->model_extension_payment_card_expresspay_log->log_error("notify", "Access is denied");
                    return;
                }
            }
            else{
                self::notify_success($dataJSON);
            }
        }
        else{
            header('HTTP/1.1 405 Method Not Allowed');
            echo 'FAILED | request method not supported';
            return;
        }
    }

    private function notify_success($dataJSON)
    {
        // Преобразование из json в array
        $data = array();
        try {
            $data = json_decode($dataJSON,true); 
        } 
        catch(Exception $e) {
            header('HTTP/1.1 400 Bad Request');
            echo 'FAILED | Failed to decode data';
            $this->model_extension_payment_card_expresspay_log->log_error("notify_success", "Failed to decode data");
            return;
        }

        $accountNo = $data['AccountNo'];
        if(isset($accountNo)){
            $this->load->model('checkout/order');
            $cmdtype    = $data['CmdType'];
            $status     = $data['Status'];
            $amount     = $data['Amount'];

            switch ($cmdtype) {
                case 1:
                    header("HTTP/1.1 200 OK");
                    echo 'OK | the notice is processed';
                    $this->model_extension_payment_card_expresspay_log->log_info("notify_success", "the notice is processed");
                    return;
                case 2:
                    $this->model_checkout_order->addOrderHistory($accountNo, $this->config->get(self::FAIL_STATUS_ID_PARAM_NAME));
                    header("HTTP/1.1 200 OK");
                    echo 'OK | the notice is processed';
                    $this->model_extension_payment_card_expresspay_log->log_info("notify_success", "the notice is processed");
                    return;
                case 3:
                    if(isset($status)){
                        switch($status){
                            case 1: // Ожидает оплату
                                if($this->model_checkout_order->getOrder($accountNo)['order_status_id'] != $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME)){
                                    $this->model_checkout_order->addOrderHistory($accountNo, $this->config->get(self::PROCESSED_STATUS_ID_PARAM_NAME));
                                }
                                break;
                            case 2: // Просрочен
                                $this->model_checkout_order->addOrderHistory($accountNo, $this->config->get(self::FAIL_STATUS_ID_PARAM_NAME));
                                break;
                            case 3: // Оплачен
                            case 6: // Оплачен с помощью банковской карты
                                $this->model_checkout_order->addOrderHistory($accountNo, $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME));
                                break;
                            case 4: // Оплачен частично
                                break;
                            case 5: // Отменен
                                $this->model_checkout_order->addOrderHistory($accountNo, $this->config->get(self::FAIL_STATUS_ID_PARAM_NAME));
                                break;
                            case 7: // Платеж возращен
                                break;
                            default:
                                header('HTTP/1.1 400 Bad Request');
                                echo'FAILED | invalid status'; //Ошибка в параметрах
                                $this->model_extension_payment_card_expresspay_log->log_error("notify_success", "Invalid status; Status - ".$status);
                                return;
                        }
                        header("HTTP/1.1 200 OK");
                        echo 'OK | the notice is processed';
                        $this->model_extension_payment_card_expresspay_log->log_info("notify_success", "the notice is processed");
                        return;
                    }
                    break;
                default:
                    header('HTTP/1.1 400 Bad Request');
                    echo'FAILED | invalid cmdtype';
                    $this->model_extension_payment_card_expresspay_log->log_error("notify_success", "Invalid cmdtype; CmdType - ".$cmdtype);
                    return;
                }
        }
        header('HTTP/1.1 400 Bad Request');
        echo 'FAILED | The notice is not processed';
        $this->model_extension_payment_card_expresspay_log->log_error("notify_success", "The notice is not processed");
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
    private function computeSignature($signatureParams, $secretWord, $method)
    {
        $normalizedParams = array_change_key_case($signatureParams, CASE_LOWER);
        $mapping = array(
            "notification"         => array(
                "data"
            )
        );
        $apiMethod = $mapping[$method];
        $result = "";
        foreach ($apiMethod as $item) {
            $result .= $normalizedParams[$item];
        }
        $hash = strtoupper(hash_hmac('sha1', $result, $secretWord));
        return $hash;
    }
}