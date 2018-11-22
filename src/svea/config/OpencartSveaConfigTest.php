<?php

/**
 * Description of OpencartSveaConfigTest
 *
 * @author anne-hal
 */
class OpencartSveaConfigTest implements \Svea\WebPay\Config\ConfigurationProvider
{

    public $config;
    public $payment_method;

    private $paymentString = "payment_";

    public function __construct($config, $payment_method = NULL)
    {
        $this->config = $config;
        $this->payment_method = $payment_method;
    }
    public function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->paymentString = "";
        }
    }

    public function getEndPoint($type)
    {
        $type = strtoupper($type);
        if ($type == "HOSTED") {
            return Svea\WebPay\Config\ConfigurationService::SWP_TEST_URL;
        } elseif ($type == "INVOICE" || $type == "PAYMENTPLAN") {
            return Svea\WebPay\Config\ConfigurationService::SWP_TEST_WS_URL;
        } elseif ($type == "HOSTED_ADMIN") {
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

    public function getMerchantId($type, $country)
    {
        $this->setVersionStrings();
        $card = $this->config->get($this->paymentString . 'svea_card_merchant_id_test');
        if ($card == "") {
            return $this->config->get($this->paymentString . 'svea_directbank_merchant_id_test');
        } else {
            return $card;
        }
    }

    public function getPassword($type, $country)
    {
        $this->setVersionStrings();
        $country = strtoupper($country);
        $lowertype = strtolower($type);
        if ($lowertype == "paymentplan") {
            return $this->config->get($this->paymentString . 'svea_partpayment_password_' . $country);
        }
        return $this->config->get($this->paymentString . 'svea_' . $lowertype . '_password_' . $country);
    }

    public function getSecret($type, $country)
    {
        $this->setVersionStrings();
        $secret = $this->config->get($this->paymentString . 'svea_card_sw_test');
        if ($secret == "") {
            return $this->config->get($this->paymentString . 'svea_directbank_sw_test');
        } else {
            return $secret;
        }
    }

    public function getUsername($type, $country)
    {
        $this->setVersionStrings();
        $country = strtoupper($country);
        $lowertype = strtolower($type);
        if ($lowertype == "paymentplan") {
            return $this->config->get($this->paymentString . 'svea_partpayment_username_' . $country);
        }
        return $this->config->get($this->paymentString. 'svea_' . $lowertype . '_username_' . $country);

    }

    public function getClientNumber($type, $country)
    {
        $this->setVersionStrings();
        $country = strtoupper($country);
        $lowertype = strtolower($type);
        if ($lowertype == "paymentplan") {
            return $this->config->get($this->paymentString . 'svea_partpayment_clientno_' . $country);
        }
        return $this->config->get($this->paymentString . 'svea_' . $lowertype . '_clientno_' . $country);
    }

    public function getIntegrationCompany()
    {
        return "Svea Ekonomi AB";
    }

    public function getIntegrationPlatform()
    {
        return 'Opencart ' . VERSION;
    }

    public function getIntegrationVersion()
    {
        return $this->config->get($this->payment_method . '_version');
    }

    public function getCheckoutMerchantId($country = NULL){}

    public function getCheckoutSecret($country = NULL){}
}