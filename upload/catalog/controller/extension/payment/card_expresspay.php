<?php

/**
 * @package       ExpressPay Payment Module for OpenCart
 * @author        ООО "ТриИнком" <info@express-pay.by>
 * @copyright     (c) 2022 Экспресс Платежи. Все права защищены.
 */

class ControllerExtensionPaymentCardExpresspay extends Controller{
    const MESSAGE_SUCCESS_PARAM_NAME                = 'payment_card_expresspay_message_success';
    const PROCESSED_STATUS_ID_PARAM_NAME            = 'payment_card_expresspay_processed_status_id';
    const SUCCESS_STATUS_ID_PARAM_NAME              = 'payment_card_expresspay_success_status_id';
    const FAIL_STATUS_ID_PARAM_NAME                 = 'payment_card_expresspay_fail_status_id';

    public function index()
    {
        $this->load->model('extension/payment/card_expresspay');
        $this->load->model('extension/payment/card_expresspay_log');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading'] = $this->language->get('text_loading');

        $data = $this->model_extension_payment_card_expresspay->setParams($data, $this->config);

        $this->model_extension_payment_card_expresspay_log->log_info("index", "DATA: " . json_encode($data));

        return $this->load->view('extension/payment/card_expresspay', $data);
    }

    public function confirm()
    {
        return true;
    }

    public function success()
    {
        $this->cart->clear();
        $this->load->model('extension/payment/card_expresspay');
        $this->load->model('extension/payment/card_expresspay_log');
        $this->load->language('extension/payment/card_expresspay');
        $this->model_extension_payment_card_expresspay_log->log_info("successStart", "Order Id: " . $this->session->data['order_id']);
        $headingTitle = $this->language->get('heading_title_success');
        $this->document->setTitle($headingTitle);
        $data['heading_title'] = $headingTitle;

        if (empty($this->config->get(self::MESSAGE_SUCCESS_PARAM_NAME))) {
            $data['text_message'] = $this->language->get('text_message_success');
        } else {
            $data['text_message'] = $this->config->get(self::MESSAGE_SUCCESS_PARAM_NAME);
        }
        $data['text_message'] = nl2br(str_replace('##order_id##', $this->session->data['order_id'], $data['text_message']));


        $data['button_continue'] = $this->language->get('button_continue');
        $data['text_loading'] = $this->language->get('text_loading');


        $this->load->model('checkout/order');
        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get(self::PROCESSED_STATUS_ID_PARAM_NAME));

        $data = $this->setBreadcrumbs($data);
        $data = $this->setButtons($data);
        $data = $this->setController($data);
        $data['continue'] = $this->url->link('common/home');

        $this->model_extension_payment_card_expresspay_log->log_info("successFinish", "DATA: " . json_encode($data));
        $this->response->setOutput($this->load->view('extension/payment/card_expresspay_successful', $data));
    }

    public function fail()
    {
        $this->load->model('extension/payment/card_expresspay_log');
        $this->load->language('extension/payment/card_expresspay');
        $this->model_extension_payment_card_expresspay_log->log_info("failStart", "Order Id: " . $this->session->data['order_id']);
        $headingTitle = $this->language->get('heading_title_fail');
        $this->document->setTitle($headingTitle);
        $data['heading_title'] = $headingTitle;

        $data['text_message'] = nl2br(str_replace('##order_id##', $this->session->data['order_id'], $this->language->get('text_message_fail')));

        $this->load->model('checkout/order');
        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get(self::FAIL_STATUS_ID_PARAM_NAME));

        $data = $this->setBreadcrumbs($data);
        $data = $this->setButtons($data);
        $data = $this->setController($data);
        $data['continue'] = $this->url->link('checkout/checkout');

        $this->model_extension_payment_card_expresspay_log->log_info("failFinish", "DATA: " . json_encode($data));
        $this->response->setOutput($this->load->view('extension/payment/card_expresspay_failure', $data));
    }

    private function setButtons($data)
    {
        $data['button_continue'] = $this->language->get('button_continue');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['continue'] = $this->url->link('checkout/checkout');

        return $data;
    }

    private function setController($data)
    {
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        return $data;
    }

    private function setBreadcrumbs($data)
    {
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'href'      => $this->url->link('common/home'),
            'text'      => $this->language->get('text_home'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'href'      => $this->url->link('checkout/cart'),
            'text'      => $this->language->get('text_basket'),
            'separator' => $this->language->get('text_separator')
        );

        $data['breadcrumbs'][] = array(
            'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
            'text'      => $this->language->get('text_checkout'),
            'separator' => $this->language->get('text_separator')
        );

        return $data;
    }
}
