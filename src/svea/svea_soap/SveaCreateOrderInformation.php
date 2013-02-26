<?php
class SveaCreateOrderInformation {

    public $ClientOrderNumber;
    public $OrderRows = array();//contains orderRows objects
    public $CustomerIdentity;
    public $OrderDate;
    public $AddressSelector;
    public $CustomerReference;
    public $OrderType;

    /**
     * Sets Variable if contains CampaignCode for Paymentplan
     * @param type $CampaignCode
     */
    public function __construct($CampaignCode = ""){
        $this->OrderRows['OrderRow'] = array();
       if($CampaignCode != ""){
           $this->CreatePaymentPlanDetails = array(
               "CampaignCode" => $CampaignCode,
               "SendAutomaticGiroPaymentForm" => 0
           );
       }
    }
    
    public function addOrderRow($orderRow){
        array_push($this->OrderRows['OrderRow'], $orderRow);
    }
    
}
?>