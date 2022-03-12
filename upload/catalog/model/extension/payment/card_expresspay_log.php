<?php

/**
 * 
 * Сохранение сообщений в лог
 * 
 */
class ModelExtensionPaymentCardExpressPayLog extends Model
{
    private static $model;

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function log_error_exception($name, $message, $e)
    {
        $this->log($name, "ERROR", $message . '; EXCEPTION MESSAGE - ' . $e->getMessage() . '; EXCEPTION TRACE - ' . $e->getTraceAsString());
    }

    public function log_error($name, $message)
    {
        $this->log($name, "ERROR", $message);
    }

    public function log_info($name, $message)
    {
        $this->log($name, "INFO", $message);
    }

    public function log($name, $type, $message)
    {
        $log_url = 'system/storage/logs/card_expresspay';

        if (!file_exists($log_url)) {
            $is_created = mkdir($log_url, 0777);

            if (!$is_created)
                return;
        }

        $log_url .= '/express-pay-' . date('Y.m.d') . '.log';
        
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
        file_put_contents($log_url, $type . " - IP - " . $_SERVER['REMOTE_ADDR'] . "; DATETIME - " . date("Y-m-d H:i:s") . "; USER AGENT - " . $userAgent . "; FUNCTION - " . $name . "; MESSAGE - " . $message . ';' . PHP_EOL, FILE_APPEND);
    }
}