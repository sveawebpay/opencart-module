<?php


class OpencartSveaCheckoutConfig implements \Svea\WebPay\Config\ConfigurationProvider
{
    public $config;
    public $payment_method;

    public function __construct($config, $payment_method = NULL)
    {
        $this->config = $config;
        $this->payment_method = $payment_method;
    }

    public function getEndpoint($type)
    {
        $type = strtoupper($type);

        if ($type == "HOSTED") {
            return Svea\WebPay\Config\ConfigurationService::SWP_PROD_URL;
        } elseif ($type == "INVOICE" || $type == "PAYMENTPLAN") {
            return Svea\WebPay\Config\ConfigurationService::SWP_PROD_WS_URL;
        } elseif ($type == "HOSTED_ADMIN") {
            return Svea\WebPay\Config\ConfigurationService::SWP_PROD_HOSTED_ADMIN_URL;
        } elseif ($type == "ADMIN") {
            return Svea\WebPay\Config\ConfigurationService::SWP_PROD_ADMIN_URL;
        } elseif ($type == 'CHECKOUT') {
            return Svea\WebPay\Config\ConfigurationService::CHECKOUT_PROD_BASE_URL;
        } else {
            throw new Exception('Invalid type. Accepted values: INVOICE, PAYMENTPLAN, HOSTED_ADMIN or HOSTED');
        }
    }

    public function getUsername($type, $country){}

    public function getPassword($type, $country){}

    public function getClientNumber($type, $country)
    {
        if (isset($GLOBALS['sveaClientNumber'])) {
            return $GLOBALS['sveaClientNumber'];
        } else {
            throw new Exception('Invalid Client Number, use API to get subsytem info for checkout order!');
        }
    }

    public function getMerchantId($type, $country){}

    public function getSecret($type, $country){}

    public function getCheckoutMerchantId()
    {
        return $this->config->get('sco_checkout_merchant_id');
    }

    public function getCheckoutSecret()
    {
        return $this->config->get('sco_checkout_secret_word');
    }
}