<?php

class OpencartSveaCheckoutConfig implements \Svea\WebPay\Config\ConfigurationProvider
{
    public $config;
    public $payment_method;

    private $moduleString = "module_";

    public function __construct($config, $payment_method = NULL)
    {
        $this->config = $config;
        $this->payment_method = $payment_method;
    }

    public function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->moduleString = "";
        }
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
        } elseif ($type == 'CHECKOUT_ADMIN') {
            return Svea\WebPay\Config\ConfigurationService::CHECKOUT_ADMIN_PROD_BASE_URL;
        } else {
            throw new Exception('Invalid type. Accepted values: INVOICE, PAYMENTPLAN, HOSTED_ADMIN or HOSTED');
        }
    }

    public function getUsername($type, $country){}

    public function getPassword($type, $country){}

    public function getClientNumber($type, $country)
    {
        if (isset($GLOBALS['payment_sveaClientNumber'])) {
            return $GLOBALS['payment_sveaClientNumber'];
        } else {
            throw new Exception('Invalid Client Number, use API to get subsytem info for checkout order!');
        }
    }

    public function getMerchantId($type, $country){}

    public function getSecret($type, $country){}

    public function getCheckoutMerchantId($countryCode = NULL)
    {
        $this->setVersionStrings();
        if($this->config->get($this->moduleString . 'sco_checkout_merchant_id_' . strtolower($countryCode)) !== NULL)
        {
            return $this->config->get($this->moduleString . 'sco_checkout_merchant_id_' . strtolower($countryCode));
        }
        else
        {
            throw new Exception('Could not fetch Merchant Id');
        }
    }

    public function getCheckoutSecret($countryCode = NULL)
    {
        $this->setVersionStrings();
        if($this->config->get($this->moduleString . 'sco_checkout_secret_word_' . strtolower($countryCode)) !== NULL)
        {
            return $this->config->get($this->moduleString . 'sco_checkout_secret_word_' . strtolower($countryCode));
        }
        else
        {
            throw new Exception('Could not fetch secret word');
        }
    }
}