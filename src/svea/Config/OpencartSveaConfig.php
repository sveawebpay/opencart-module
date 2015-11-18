<?php
$root = realpath(dirname(__FILE__));
require_once "$root/../Includes.php";

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OpencartSveaConfig
 *
 * @author anne-hal
 */
class OpencartSveaConfig implements ConfigurationProvider{

    public $config;
    public $payment_method;

    public function __construct($config, $payment_method = NULL) {
        $this->config = $config;
        $this->payment_method = $payment_method;
    }

    public function getEndPoint($type) {
        $type = strtoupper($type);
        if($type == "HOSTED"){
            return Svea\SveaConfig::SWP_PROD_URL;;
        }elseif($type == "INVOICE" || $type == "PAYMENTPLAN"){
            return Svea\SveaConfig::SWP_PROD_WS_URL;
        }elseif($type == "HOSTED_ADMIN"){
            return Svea\SveaConfig::SWP_PROD_HOSTED_ADMIN_URL;
        }elseif ($type == "ADMIN") {
            return Svea\SveaConfig::SWP_PROD_ADMIN_URL;
        } else {
            throw new Exception('Invalid type. Accepted values: INVOICE, PAYMENTPLAN, HOSTED_ADMIN or HOSTED');
        }
    }

    public function getMerchantId($type, $country) {
        $card = $this->config->get('svea_card_merchant_id_prod');
        if($card == ""){
            return $this->config->get('svea_directbank_merchant_id_prod');
        }else{
            return $card;
        }
    }

    public function getPassword($type, $country) {
        $country = strtoupper($country);
        $lowertype = strtolower($type);
        if($lowertype == "paymentplan"){
             return $this->config->get('svea_partpayment_password_' . $country);
        }
        return $this->config->get('svea_'.$lowertype.'_password_' . $country);
    }

    public function getSecret($type, $country) {
        $secret = $this->config->get('svea_card_sw_prod');
        if($secret == ""){
            return $this->config->get('svea_directbank_sw_prod');
        }  else {
            return $secret;
        }
    }

    public function getUsername($type, $country) {
        $country = strtoupper($country);
        $lowertype = strtolower($type);
        if($lowertype == "paymentplan"){
             return $this->config->get('svea_partpayment_username_' . $country);
        }
        return $this->config->get('svea_'.$lowertype.'_username_' . $country);

    }

    public function getclientNumber($type, $country) {
        $country = strtoupper($country);
        $lowertype = strtolower($type);
        if($lowertype == "paymentplan"){
            return $this->config->get('svea_partpayment_clientno_' . $country);
        }
        return $this->config->get('svea_'.$lowertype.'_clientno_' . $country);
    }

    public function getIntegrationCompany() {
        return "Svea Ekonomi : Opencart 2 module";
    }
    public function getIntegrationPlatform() {
        return 'Opencart '. VERSION;
    }
    public function getIntegrationVersion() {
        return $this->config->get($this->payment_method . '_version');
    }
}

?>
