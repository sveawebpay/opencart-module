<?php
/**
 *@package com.epayment.util.implementation;
 */
class SveaPaymentResponse {
	
	public $transactionId;
	public $statuscode;
	public $paymentMethod;
	public $merchantId;
	public $customerRefno;
	public $amount;
	public $currency;
	public $payment;
	public $legalName;
	public $ssn;
	public $addressLine1;
	public $addressLine2;
	public $postCode;
	public $postArea;
	public $cardType;
	public $maskedCardNo;
	public $authCode;
	
	public function __construct($encodedMessage = NULL) 
	{
		if($encodedMessage != NULL){
			$this->payment = $encodedMessage;
			$decodedMessage = base64_decode($this->payment);
			$this->processXml($decodedMessage);
		}	
	}
	
	public function processXml($xmlInput)
	{
		$simpleXml = new SimpleXMLElement($xmlInput);
		$this->transactionId = (string)$simpleXml->transaction["id"];
		$this->statuscode = (string)$simpleXml->statuscode;
		$this->paymentMethod = (string)$simpleXml->transaction->paymentmethod;
		$this->merchantId = (string)$simpleXml->transaction->merchantid;
		$this->customerRefno = (string)$simpleXml->transaction->customerrefno;
		$this->amount = (string)$simpleXml->transaction->amount;
		$this->currency = (string)$simpleXml->transaction->currency;
		$this->legalName = (string)$simpleXml->transaction->customer->legalname;
		$this->ssn = (string)$simpleXml->transaction->customer->ssn;
		$this->addressLine1 = (string)$simpleXml->transaction->customer->addressline1;
		$this->addressLine2 = (string)$simpleXml->transaction->customer->addressline2;
		$this->postCode = (string)$simpleXml->transaction->customer->postcode;
		$this->postArea = (string)$simpleXml->transaction->customer->postarea;
		$this->cardType = (string)$simpleXml->transaction->cardtype;
		$this->maskedCardNo = (string)$simpleXml->transaction->maskedcardno;
		$this->authCode = (string)$simpleXml->transaction->authcode;
	}
	
	public function validateMac($macToValidate,$secret=null){
		if($secret == null){
			//$secret = $secretWord;
		}
		$macString = hash("SHA512",$this->payment.$secret);

		if($macToValidate == $macString){
			return true;
		} 
		return false;
	}
	
}