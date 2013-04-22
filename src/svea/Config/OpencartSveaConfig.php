<?php
require_once '/../Includes.php';
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

    public function __construct($config) {
        $this->config = $config;
    }

    public function getEndPoint($type) {
        $type = strtoupper($type);
          if($type == "HOSTED"){
            return   SveaConfig::SWP_TEST_URL;
        }elseif($type == "INVOICE" || $type == "PAYMENTPLAN"){
             return SveaConfig::SWP_TEST_WS_URL;
        }  else {
           throw new Exception('Invalid type. Accepted values: INVOICE, PAYMENTPLAN or HOSTED');
        }
    }

    public function getMerchantId($type, $country) {

        $country = strtoupper($country);
        $card = $this->config->get('svea_card_merchant_id_prod_'.$country);
        if($card == ""){
            return $this->config->get('svea_directbank_merchant_id_prod_'.$country);
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
        $country = strtoupper($country);
        $secret = $this->config->get('svea_card_sw_prod_'.$country);
        if($secret == ""){
            return $this->config->get('svea_directbank_sw_prod'.$country);
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
