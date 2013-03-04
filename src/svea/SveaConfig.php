<?php 
/**
 * 
 * This class is used to configure merchant specific details for 
 * sveas request classes. Including this class will register an autoloader for all classes in this lib.
 * Change methods in thiss class if you want to store configs in DB or file etc
 * @author Svea Ekonomi
 * @package com.epayment.util.implementation;
 *
 */
if (!defined('WEBPAY_DIR')) define('WEBPAY_DIR', dirname(__FILE__));
//spl_autoload_register(array('SveaConfig', 'autoload'));
include(WEBPAY_DIR."/SveaAddress.php");
        include(WEBPAY_DIR."/SveaCampaign.php");
        include(WEBPAY_DIR."/SveaOrder.php");
        include(WEBPAY_DIR."/SveaOrderRow.php");
        require(WEBPAY_DIR."/SveaPaymentRequest.php");
        include(WEBPAY_DIR."/SveaPaymentResponse.php");
        include(WEBPAY_DIR."/SveaXMLBuilder.php");
class SveaConfig {
	
	public $merchantId;
	public $secret;
	
	public $testMode = false;
	
	const SWP_TEST_URL = "https://test.sveaekonomi.se/webpay/payment";
	const SWP_PROD_URL = "https://webpay.sveaekonomi.se/webpay/payment";
	
	public function setTestMode($test){
		$this->testMode = $test;
	}
	
	public function getTestMode(){
	   echo WEBPAY_DIR;
		return $this->testMode;
	}
	
	/**
	 * Singleton instance holder
	 * @var SveaConfig
	 */
	protected static $_instance;
	
	/**
	 * Change this function if you want to store your details in a db or on file etc.
	 */
	public function __construct(){
		$this->merchantId = 0000; //Set your merchant ID here
		$this->secret = "d"; //Set your secret word here
	}
	
	
	/**
	 * Get an instance of the Config
	 * @return SveaConfig
	 */
	public static function getConfig()
	{
		    if (!isset(self::$_instance)) 
		    {
            	$c = __CLASS__;
            	self::$_instance = new $c;
        	}
        return self::$_instance;
	}
	
	public static function configure($object){
		$inst = self::getConfig();
		if(property_exists($object, "merchantId")){
			$object->merchantId = $inst->merchantId;
		}
		if(property_exists($object, "merchantid")){
			$object->merchantid = $inst->merchantId;
		}
		if(property_exists($object, "secret")){
			$object->merchantId = $inst->secret;
		}
	}
	
}