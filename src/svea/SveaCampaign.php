<?php
/**
 * Class representing an installment campaign
 * @package com.epayment.util.implementation;
 *
 */
class SveaCampaign{
	
	/**
	 * Can be sent in the payment request if campaign choice is made at the merchant.
	 * @var integer
	 */
	public $campaignCode;
	/**
	 * Text description that can be displayed to the customer.
	 * @var string
	 */
	public $description;
	/**
	 *  Type of installmen
	 * @var string|const
	 */
	public $paymentPlanType;
	/**
	 * Monthly payment (dispatch fee not included)
	 * Not used in the case of InterestAndAmortizationFree since there is only one payment
	 * @var int
	 */
	public $montlyAnnuity;
	/**
	 * Initial fee. Is paid at first installment occasion
	 * @var string
	 */
	public $initialFee;
	/**
	 *  Notification fee which is debited at each occasion of installment
	 * @var int
	 */
	public $notificationFee;
	/**
	 * Yearly interest in percent.
	 * @var double
	 */
	public $interestRatePercent;
	/**
	 *  Effective interest in percent. We recommend you not to show the effective interest rate at the 
	 *  counter and by product, as this could be very high at low amounts. 
	 *  We suggest that you show the finished examples instead
	 * @var double
	 */
	public $effectiveInterestRatePercent;
	
	/**
	 * Standard annuity loan
	 * @var string
	 */
	const STANDARD = "Standard";
	/**
	 * Interest-free annuity loan
	 * @var string
	 */
	const INTERESTFREE = "InterestFree";
	/**
	 * Interest and Amortizationfree loan. In reality this means that 
	 * the standard conditions are that one payment is made on the entire sum at the end of the 
	 * interest and amortization-free period.
	 * @var string
	 */
	const INTERESTANDAMORTIZATIONFREE = "InterestAndAmortizationFree";
	
	
}