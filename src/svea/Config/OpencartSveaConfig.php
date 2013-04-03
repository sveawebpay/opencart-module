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
          if($type == "HOSTED"){
            return   SveaConfig::SWP_TEST_URL;
        }elseif($type == "INVOICE" || $type == "PAYMENTPLAN"){
             SveaConfig::SWP_TEST_WS_URL;
        }  else {
           throw new Exception('Invalid type. Accepted values: INVOICE, PAYMENTPLAN or HOSTED');
        }          
    }

    public function getMerchantId($type, $country) {
        return $this->config->get('svea_card_merchant_id');
    }

    public function getPassword($type, $country) {
        $lowertype = strtolower($type);
        return$this->config->get('svea_'.$lowertype.'_password_' . $country);
    }

    public function getSecret($type, $country) {
        return "secret";
    }

    public function getUsername($type, $country) {
        $lowertype = strtolower($type);
        return $this->config->get('svea_'.$lowertype.'_username_' . $country);
     
    }

    public function getclientNumber($type, $country) {
        $lowertype = strtolower($type);
        return $this->config->get('svea_'.$lowertype.'_clientno_' . $country);
    }
}

?>
