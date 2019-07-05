<?php

class OpencartSveaCheckoutConfigTest implements \Svea\WebPay\Config\ConfigurationProvider
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

        if($type == "HOSTED"){
            return Svea\WebPay\Config\ConfigurationService::SWP_TEST_URL;
        } elseif($type == "INVOICE" || $type == "PAYMENTPLAN"){
            return Svea\WebPay\Config\ConfigurationService::SWP_TEST_WS_URL;
        } elseif($type == "HOSTED_ADMIN"){
            return Svea\WebPay\Config\ConfigurationService::SWP_TEST_HOSTED_ADMIN_URL;
        } elseif ($type == "ADMIN") {
            return Svea\WebPay\Config\ConfigurationService::SWP_TEST_ADMIN_URL;
        } elseif ($type == 'CHECKOUT') {
            return Svea\WebPay\Config\ConfigurationService::CHECKOUT_TEST_BASE_URL;
        } elseif ($type == 'CHECKOUT_ADMIN') {
            return Svea\WebPay\Config\ConfigurationService::CHECKOUT_ADMIN_TEST_BASE_URL;
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

    public function getCheckoutMerchantId($country = NULL)
    {
        $this->setVersionStrings();
        if(VERSION < 3.0)
        {
            if($this->config->config->get($this->moduleString . 'sco_checkout_test_merchant_id_' . strtolower($country)) !== NULL)
            {
                return $this->config->config->get($this->moduleString . 'sco_checkout_test_merchant_id_' . strtolower($country));
            }
            else
            {
                throw new Exception('Could not fetch Merchant Id. CountryCode: ' . $country . ' Environment: Stage');
            }
        }
        else
        {
            $this->config->load->model('setting/setting');
            if ($this->config->model_setting_setting->getSettingValue($this->moduleString . 'sco_checkout_test_merchant_id_' . strtolower($country)) !== NULL)
            {
                return $this->config->model_setting_setting->getSettingValue($this->moduleString . 'sco_checkout_test_merchant_id_' . strtolower($country));
            }
            else
            {
                throw new Exception('Could not fetch Merchant Id. CountryCode: ' . $country . ' Environment: Stage');
            }
        }
    }

    public function getCheckoutSecret($country = NULL)
    {
        $this->setVersionStrings();
        if(VERSION < 3.0)
        {
            if($this->config->config->get($this->moduleString . 'sco_checkout_test_secret_word_' . strtolower($country)) !== NULL)
            {
                return $this->config->config->get($this->moduleString . 'sco_checkout_test_secret_word_' . strtolower($country));
            }
            else
            {
                throw new Exception('Could not fetch secret word. CountryCode: ' . $country . ' Environment: Stage');
            }
        }
        else
        {
            $this->config->load->model('setting/setting');
            if ($this->config->model_setting_setting->getSettingValue($this->moduleString . 'sco_checkout_test_secret_word_' . strtolower($country)) !== NULL)
            {
                return $this->config->model_setting_setting->getSettingValue($this->moduleString . 'sco_checkout_test_secret_word_' . strtolower($country));
            }
            else
            {
                throw new Exception('Could not fetch secret word. CountryCode: ' . $country . ' Environment: Stage');
            }
        }
    }
}