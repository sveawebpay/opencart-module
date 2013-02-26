<?php
/**
 *@package com.epayment.util.implementation;
 */
class SveaPaymentRequest {
	
	public $merchantid;
	public $payment;
	public $mac;
	public $xml;

	public $secret;
	/**
	 * @var SveaOrder
	 */
	public $order;
	/**
	 * Construct with base data. If no params are given, merchant id and secret will be set from SveaConfig
	 * @param integer merchantid
	 * @param string secret
	 */
	public function __construct($merchantid=null,$secret=null){
		if($merchantid == null || $secret || null){
			$config = SveaConfig::getConfig();
			$this->merchantid = $config->merchantId;
			$this->secret = $config->secret;
		}else{
			$this->merchantid = $merchantid;
			$this->secret = $secret;
		}
	}
	
	/**
	 * Generate payment-param from order
	 * order and secret must be set before this method is used. 
	 * Throws Exception if order or secret is not set.
	 * @throws Exception
	 */
	public function createPaymentMessage() {
		if($this->order == null || $this->secret == null) {
			throw new Exception("Order and secret must be set for this operation");
		}
		$XMLBuilder = new SveaXMLBuilder();
		$this->xml = $XMLBuilder->getOrderXML($this->order);
		$this->payment = base64_encode($this->xml);
		$this->mac = hash("SHA512", $this->payment.$this->secret);

	}
	
	public function getPaymentForm($testmode) {
		
		$formString = "<form name='paymentForm' id='paymentForm' method='post' action='";
		$formString .= ($testmode) ? SveaConfig::SWP_TEST_URL : SveaConfig::SWP_PROD_URL;
		$formString .= "'>";
		$formString .= "<input type='hidden' name='merchantid' value='{$this->merchantid}'/>";
		$formString .= "<input type='hidden' name='message' value='{$this->payment}'/>";
		$formString .= "<input type='hidden' name='mac' value='{$this->mac}'/>";
        //$formString .= '<noscript><p>Javascript is inactivated in your browser, you will manually have to redirect to the payapge</p><input type="submit" name="submit" value="Skicka till Svea Webpay"/></noscript>';
		//$formString .= '<input type="submit" name="submit" value="Skicka till Svea Webpay"/>';
        $formString .= "</form>";
		return $formString;
	}
	
}