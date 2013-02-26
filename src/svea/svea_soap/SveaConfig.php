<?php

/**
 * Autoload all classes
 */
if (!defined('SVEA_DIR'))
    define('SVEA_DIR', dirname(__DIR__));
if (!defined('SVEA_REQUEST_DIR'))
    define('SVEA_REQUEST_DIR', dirname(__FILE__));
//spl_autoload_register(array('SveaConfig', 'autoload'));
include(SVEA_REQUEST_DIR . "/SveaSoapArrayBuilder.php");
include(SVEA_REQUEST_DIR . "/SveaDoRequest.php");
include(SVEA_DIR . "/SveaRequest.php");
include(SVEA_DIR . "/SveaAddress.php");
include(SVEA_DIR . "/SveaAuth.php");
include(SVEA_DIR . "/SveaCreateOrderInformation.php");
include(SVEA_DIR . "/SveaCustomerIdentity.php");
include(SVEA_DIR . "/SveaIdentity.php");
include(SVEA_DIR . "/SveaOrder.php");
require(SVEA_DIR . "/SveaOrderRow.php");
include(SVEA_DIR . "/SveaDeliverInvoiceDetails.php");
include(SVEA_DIR . "/SveaDeliverOrderInformation.php");
include(SVEA_DIR . "/SveaDeliverOrder.php");

class SveaConfig {

    public $testMode = true;
    public $svea_server;
   
    
    /**
     * Singleton instance holder
     * @var SveaConfig
     */
    protected static $_instance;

    public function __construct() {
        if ($this->testMode == true) {
            $this->svea_server = "https://webservices.sveaekonomi.se/webpay_test/SveaWebPay.asmx?WSDL";
        } else {
            $this->svea_server = "https://webservices.sveaekonomi.se/webpay/SveaWebPay.asmx?WSDL";
        }
    }

    public function setTestMode($test) {
        $this->testMode = $test;
    }
    public function getTestMode(){
		return $this->testMode;
	}
    /**
     * Get an instance of the Config
     * @return SveaConfig
     */
    public static function getConfig() {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }

}

?>