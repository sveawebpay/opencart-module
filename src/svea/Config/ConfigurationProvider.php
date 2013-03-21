<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
interface ConfigurationProvider {
    public function getUsername($type, $country);
    public function getPassword($type, $country);
}

/**
 * Description of ConfigurationProvider
 *
 * @author anne-hal
 */
class SveaConfigurationProvider implements ConfigurationProvider {
    
    public $environment;

    public function __construct($enviromentConfig) {
        $this->environment = $enviromentConfig;
    }

    public function getUsername($type, $country) {
        return $this->environment['credentials'][$country]['auth']['INVOICE']['username'];
    }

    public function getPassword($type, $country) {
        return $this->environment['credentials'][$country]['auth']['PAYMENTPLAN']['password'];
    }
    //put your code here
}

?>
