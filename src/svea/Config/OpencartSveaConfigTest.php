<?php
$root = realpath(dirname(__FILE__));
require_once "$root/../Includes.php";

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OpencartSveaConfigTest
 *
 * @author anne-hal
 */
class OpencartSveaConfigTest implements ConfigurationProvider{

    public $config;

    public function __construct($config) {
        $this->config = $config;
    }

        public function getEndPoint($type) {
       $type = strtoupper($type);
        if($type == "HOSTED"){
            return Svea\SveaConfig::SWP_TEST_URL;;
        } elseif($type == "INVOICE" || $type == "PAYMENTPLAN"){
            return Svea\SveaConfig::SWP_TEST_WS_URL;
        } elseif($type == "HOSTED_ADMIN"){
            return Svea\SveaConfig::SWP_TEST_HOSTED_ADMIN_URL;
        } elseif ($type == "ADMIN") {
            return Svea\SveaConfig::SWP_TEST_ADMIN_URL;
        } else {
            throw new Exception('Invalid type. Accepted values: INVOICE, PAYMENTPLAN, HOSTED_ADMIN or HOSTED');
        }

    }

    public function getMerchantId($type, $country) {
        $card = $this->config->get('svea_card_merchant_id_test');
        if($card == ""){
            return $this->config->get('svea_directbank_merchant_id_test');
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
        $secret = $this->config->get('svea_card_sw_test');
        if($secret == ""){
            return $this->config->get('svea_directbank_sw_test');
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
}

?>
