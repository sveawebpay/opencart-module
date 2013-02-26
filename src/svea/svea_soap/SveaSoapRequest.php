<?php
/**
 * 
 * Create SoapObject
 * Do request
 * @return Response Object
 */
class SveaSoapRequest{
  
    private $svea_server;
    public $client;

    public function __construct(){
        $config = SveaConfig::getConfig();
        $this->svea_server = $config->svea_server;
        $this->SetSoapClient();
    }
    
    private function SetSoapClient(){
        $this->client = new SoapClient($this->svea_server, array('trace' => 1));
    }
    
    /**
     * Create Invoice or Partpaymentorder
     * @param type $order Object containing SveaAuth and SveaCreateOrderInformation
     * @return CreateOrderEuResponse Object
     */
    public function CreateOrderEu($order){
        $builder = new SveaSoapArrayBuilder(); 
     return $this->client->CreateOrderEu($builder->getArray($order));
    }
    
    /**
     * Use to get Addresses based on ssn or orgnr. Only in SE, NO, DK.
     * @param type $request Object containing SveaAuth, IsCompany, CountryCode, SecurityNumber
     * @return GetCustomerAddressesResponse Object. 
     */
    public function GetAddresses($request){
        $builder = new SveaSoapArrayBuilder();
        return $this->client->GetAddresses($builder->getArray($request));
    }
    
    /**
     * Use to get params om partpayment options
     * @param type SveaAuth Object
     * @return CampaignCodeInfo Object
     */
    public function GetPaymentPlanParamsEu($auth){
        $builder = new SveaSoapArrayBuilder();
        return $this->client->GetPaymentPlanParamsEu($builder->getArray($auth));
    }
    
    /**
     * 
     * @param type $deliverdata Object containing SveaAuth and DeliverOrderInformation
     * @return DeliverOrderResult Object
     */
    public function DeliverOrderEu($deliverdata){
        $builder = new SveaSoapArrayBuilder();
        try{
        $return = $this->client->DeliverOrderEu($builder->getArray($deliverdata));
        }  catch (SoapFault $fault){
            print_r($this->client->__getLastRequest()."<hr />".$fault->getMessage());
            die();
        }
        return $this->client->DeliverOrderEu($builder->getArray($deliverdata));
    }
}
?>