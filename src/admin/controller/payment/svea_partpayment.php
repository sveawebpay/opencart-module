<?php
class ControllerPaymentsveapartpayment extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/svea_partpayment');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

                    $this->model_setting_setting->editSetting('svea_partpayment', $this->request->post);
                      //load latest PaymentPlan params from Svea api on save
                    $this->loadPaymentPlanParams();

                    $this->session->data['success'] = $this->language->get('text_success');
                    $this->redirect(HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token']);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_order_status_text'] = $this->language->get('entry_order_status_text');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['tab_general'] = $this->language->get('tab_general');
                //Credentials
                $this->data['entry_username']      = $this->language->get('entry_username');
                $this->data['entry_password']      = $this->language->get('entry_password');
                $this->data['entry_clientno']      = $this->language->get('entry_clientno');
                $this->data['entry_product']      = $this->language->get('entry_product');
                $this->data['entry_product_text'] = $this->language->get('entry_product_text');

                $this->data['entry_sweden']        = $this->language->get('entry_sweden');
                $this->data['entry_finland']       = $this->language->get('entry_finland');
                $this->data['entry_denmark']       = $this->language->get('entry_denmark');
                $this->data['entry_norway']        = $this->language->get('entry_norway');
                $this->data['entry_germany']       = $this->language->get('entry_germany');
                $this->data['entry_netherlands']   = $this->language->get('entry_netherlands');

                $this->data['entry_testmode']      = $this->language->get('entry_testmode');
                $this->data['entry_auto_deliver']  = $this->language->get('entry_auto_deliver');
                $this->data['entry_auto_deliver_text'] = $this->language->get('entry_auto_deliver_text');
                $this->data['entry_post']           = $this->language->get('entry_post');
                $this->data['entry_email']          = $this->language->get('entry_email');
                $this->data['entry_yes']           = $this->language->get('entry_yes');
                $this->data['entry_no']            = $this->language->get('entry_no');
                $this->data['entry_min_amount']    = $this->language->get('entry_min_amount');

                $this->data['version']             = floatval(VERSION);

                //As you might notice the word we're really looking for is "country" not "lang". Leaving it like that so it won't ruin anything though.
                $cred = array();
                $cred[] = array("lang" => "SE","value_username" => $this->config->get('svea_partpayment_username_SE'),"name_username" => 'svea_partpayment_username_SE',"value_password" => $this->config->get('svea_partpayment_password_SE'),"name_password" => 'svea_partpayment_password_SE',"value_clientno" => $this->config->get('svea_partpayment_clientno_SE'),"name_clientno" => 'svea_partpayment_clientno_SE',"min_amount_name" => 'svea_partpayment_min_amount_SE',"min_amount_value" => $this->config->get('svea_partpayment_min_amount_SE'),"value_testmode" => $this->config->get('svea_partpayment_testmode_SE'),"name_testmode" => 'svea_partpayment_testmode_SE');
                $cred[] = array("lang" => "NO","value_username" => $this->config->get('svea_partpayment_username_NO'),"name_username" => 'svea_partpayment_username_NO',"value_password" => $this->config->get('svea_partpayment_password_NO'),"name_password" => 'svea_partpayment_password_NO',"value_clientno" => $this->config->get('svea_partpayment_clientno_NO'),"name_clientno" => 'svea_partpayment_clientno_NO',"min_amount_name" => 'svea_partpayment_min_amount_NO',"min_amount_value" => $this->config->get('svea_partpayment_min_amount_NO'),"value_testmode" => $this->config->get('svea_partpayment_testmode_NO'),"name_testmode" => 'svea_partpayment_testmode_NO');
                $cred[] = array("lang" => "FI","value_username" => $this->config->get('svea_partpayment_username_FI'),"name_username" => 'svea_partpayment_username_FI',"value_password" => $this->config->get('svea_partpayment_password_FI'),"name_password" => 'svea_partpayment_password_FI',"value_clientno" => $this->config->get('svea_partpayment_clientno_FI'),"name_clientno" => 'svea_partpayment_clientno_FI',"min_amount_name" => 'svea_partpayment_min_amount_FI',"min_amount_value" => $this->config->get('svea_partpayment_min_amount_FI'),"value_testmode" => $this->config->get('svea_partpayment_testmode_FI'),"name_testmode" => 'svea_partpayment_testmode_FI');
                $cred[] = array("lang" => "DK","value_username" => $this->config->get('svea_partpayment_username_DK'),"name_username" => 'svea_partpayment_username_DK',"value_password" => $this->config->get('svea_partpayment_password_DK'),"name_password" => 'svea_partpayment_password_DK',"value_clientno" => $this->config->get('svea_partpayment_clientno_DK'),"name_clientno" => 'svea_partpayment_clientno_DK',"min_amount_name" => 'svea_partpayment_min_amount_DK',"min_amount_value" => $this->config->get('svea_partpayment_min_amount_DK'),"value_testmode" => $this->config->get('svea_partpayment_testmode_DK'),"name_testmode" => 'svea_partpayment_testmode_DK');
                $cred[] = array("lang" => "NL","value_username" => $this->config->get('svea_partpayment_username_NL'),"name_username" => 'svea_partpayment_username_NL',"value_password" => $this->config->get('svea_partpayment_password_NL'),"name_password" => 'svea_partpayment_password_NL',"value_clientno" => $this->config->get('svea_partpayment_clientno_NL'),"name_clientno" => 'svea_partpayment_clientno_NL',"min_amount_name" => 'svea_partpayment_min_amount_NL',"min_amount_value" => $this->config->get('svea_partpayment_min_amount_NL'),"value_testmode" => $this->config->get('svea_partpayment_testmode_NL'),"name_testmode" => 'svea_partpayment_testmode_NL');
                $cred[] = array("lang" => "DE","value_username" => $this->config->get('svea_partpayment_username_DE'),"name_username" => 'svea_partpayment_username_DE',"value_password" => $this->config->get('svea_partpayment_password_DE'),"name_password" => 'svea_partpayment_password_DE',"value_clientno" => $this->config->get('svea_partpayment_clientno_DE'),"name_clientno" => 'svea_partpayment_clientno_DE',"min_amount_name" => 'svea_partpayment_min_amount_DE',"min_amount_value" => $this->config->get('svea_partpayment_min_amount_DE'),"value_testmode" => $this->config->get('svea_partpayment_testmode_DE'),"name_testmode" => 'svea_partpayment_testmode_DE');

                $this->data['credentials'] = $cred;

                $this->data['svea_partpayment_sort_order']    = $this->config->get('svea_partpayment_sort_order');
                $this->data['svea_partpayment_auto_deliver']      = $this->config->get('svea_partpayment_auto_deliver');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

  		$this->document->breadcrumbs = array();

   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=common/home&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		);

   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('text_payment'),
      		'separator' => ' :: '
   		);

   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=payment/svea_partpayment&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/svea_partpayment&token=' . $this->session->data['token'];

		$this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];

		if (isset($this->request->post['svea_partpayment_order_status_id'])) {
			$this->data['svea_partpayment_order_status_id'] = $this->request->post['svea_partpayment_order_status_id'];
		} else {
			$this->data['svea_partpayment_order_status_id'] = $this->config->get('svea_partpayment_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['svea_partpayment_geo_zone_id'])) {
			$this->data['svea_partpayment_geo_zone_id'] = $this->request->post['svea_partpayment_geo_zone_id'];
		} else {
			$this->data['svea_partpayment_geo_zone_id'] = $this->config->get('svea_partpayment_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

                //order status
		if (isset($this->request->post['svea_partpayment_status'])) {
			$this->data['svea_partpayment_status'] = $this->request->post['svea_partpayment_status'];
		} else {
			$this->data['svea_partpayment_status'] = $this->config->get('svea_partpayment_status');
		}
                //sort order
		if (isset($this->request->post['svea_partpayment_sort_order'])) {
			$this->data['svea_partpayment_sort_order'] = $this->request->post['svea_partpayment_sort_order'];
		} else {
			$this->data['svea_partpayment_sort_order'] = $this->config->get('svea_partpayment_sort_order');
		}
                //auto deliver
                if (isset($this->request->post['svea_partpayment_auto_deliver'])) {
			$this->data['svea_partpayment_auto_deliver'] = $this->request->post['svea_partpayment_auto_deliver'];
		} else {
			$this->data['svea_partpayment_auto_deliver'] = $this->config->get('svea_partpayment_auto_deliver');
		}
                 //autodeliver order status
		if (isset($this->request->post['svea_partpayment_auto_deliver_status_id'])) {
			$this->data['svea_partpayment_auto_deliver_status_id'] = $this->request->post['svea_partpayment_auto_deliver_status_id'];
		} else {
			$this->data['svea_partpayment_auto_deliver_status_id'] = $this->config->get('svea_partpayment_auto_deliver_status_id');
		}
                 //show price on product
		if (isset($this->request->post['svea_partpayment_product_price'])) {
			$this->data['svea_partpayment_product_price'] = $this->request->post['svea_partpayment_product_price'];
		} else {
			$this->data['svea_partpayment_product_price'] = $this->config->get('svea_partpayment_product_price');
		}

		$this->template = 'payment/svea_partpayment.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));


	}


        private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/svea_partpayment')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}


    /**
     * Svea: Create table if does not exists, call Svea API, load with params values for specific countrycode
     */
    private function loadPaymentPlanParams(){
        //Load SVEA includes
        include(DIR_APPLICATION . '../svea/Includes.php');
        $countryCode = array("SE","NO","FI","DK","NL","DE");
        for($i=0;$i<sizeof($countryCode);$i++){
            $username = $this->config->get('svea_partpayment_username_' . $countryCode[$i]);
            $password = $this->config->get('svea_partpayment_password_' . $countryCode[$i]);
            $cliend_id = $this->config->get('svea_partpayment_clientno_' . $countryCode[$i]);
            //get params if config is set

            if($username != "" && $password != "" && $cliend_id != ""){
                if ( $this->config->get('svea_partpayment_testmode_' . $countryCode[$i]) !== NULL)
                    $conf = $this->config->get('svea_partpayment_testmode_' . $countryCode[$i]) == "1" ? new OpencartSveaConfigTest($this->config) : new OpencartSveaConfig($this->config);
                    $svea_params = WebPay::getPaymentPlanParams($conf);
                    try {
                         $svea_params = $svea_params->setCountryCode($countryCode[$i])
                            ->doRequest();
                    } catch (Exception $e) {
                         $this->log->write($e->getMessage());
                    }
                   if(isset($svea_params->errormessage) == FALSE){
                       $formatted_params = $this->sveaFormatParams($svea_params);
                       if($formatted_params !=NULL){
                            $this->insertPaymentPlanParams($formatted_params,$countryCode[$i]);
                       }
                    }
            }

        }

    }
    /**
     * Create Svea params talbe if not exists
     */
    public function install() {
        $q  = ' CREATE TABLE IF NOT EXISTS `' . DB_PREFIX .'svea_params_table`
                (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `campaignCode` VARCHAR( 100 ) NOT NULL,
                `description` VARCHAR( 100 ) NOT NULL ,
                `paymentPlanType` VARCHAR( 100 ) NOT NULL ,
                `contractLengthInMonths` INT NOT NULL ,
                `monthlyAnnuityFactor` DOUBLE NOT NULL ,
                `initialFee` DOUBLE NOT NULL ,
                `notificationFee` DOUBLE NOT NULL ,
                `interestRatePercent` INT NOT NULL ,
                `numberOfInterestFreeMonths` INT NOT NULL ,
                `numberOfPaymentFreeMonths` INT NOT NULL ,
                `fromAmount` DOUBLE NOT NULL ,
                `toAmount` DOUBLE NOT NULL ,
                `timestamp` INT UNSIGNED NOT NULL,
                `countryCode` VARCHAR( 100 ) NOT NULL
            )   ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
            ';
        $this->db->query($q);
    }

      protected function sveaFormatParams($response){
            $result = array();
            if ($response == null) {
                return $result;
            } else {
                foreach ($response->campaignCodes as $responseResultItem) {
                    try {
                        $campaignCode = (isset($responseResultItem->campaignCode)) ? $responseResultItem->campaignCode : "";
                        $description = (isset($responseResultItem->description)) ? $responseResultItem->description : "";
                        $paymentplantype = (isset($responseResultItem->paymentPlanType)) ? $responseResultItem->paymentPlanType : "";
                        $contractlength = (isset($responseResultItem->contractLengthInMonths)) ? $responseResultItem->contractLengthInMonths : "";
                        $monthlyannuityfactor = (isset($responseResultItem->monthlyAnnuityFactor)) ? $responseResultItem->monthlyAnnuityFactor : "";
                        $initialfee = (isset($responseResultItem->initialFee)) ? $responseResultItem->initialFee : "";
                        $notificationfee = (isset($responseResultItem->notificationFee)) ? $responseResultItem->notificationFee : "";
                        $interestratepercentage = (isset($responseResultItem->interestRatePercent)) ? $responseResultItem->interestRatePercent : "";
                        $interestfreemonths = (isset($responseResultItem->numberOfInterestFreeMonths)) ? $responseResultItem->numberOfInterestFreeMonths : "";
                        $paymentfreemonths = (isset($responseResultItem->numberOfPaymentFreeMonths)) ? $responseResultItem->numberOfPaymentFreeMonths : "";
                        $fromamount = (isset($responseResultItem->fromAmount)) ? $responseResultItem->fromAmount : "";
                        $toamount = (isset($responseResultItem->toAmount)) ? $responseResultItem->toAmount : "";

                        $result[] = Array(
                            "campaignCode" => $campaignCode,
                            "description" => $description,
                            "paymentPlanType" => $paymentplantype,
                            "contractLengthInMonths" => $contractlength,
                            "monthlyAnnuityFactor" => $monthlyannuityfactor,
                            "initialFee" => $initialfee,
                            "notificationFee" => $notificationfee,
                            "interestRatePercent" => $interestratepercentage,
                            "numberOfInterestFreeMonths" => $interestfreemonths,
                            "numberOfPaymentFreeMonths" => $paymentfreemonths,
                            "fromAmount" => $fromamount,
                            "toAmount" => $toamount
                        );
                    } catch (Exception $e) {
                       $this->log->write($e->getMessage());
                    }
                }
            }
            return $result;
        }

    protected function insertPaymentPlanParams($params,$countryCode) {

            foreach ($params as $param) {
                //$query = $db->getQuery(true);
                $q = "INSERT INTO `" . DB_PREFIX ."svea_params_table`
                        (   `campaignCode` ,
                            `description`,
                            `paymentPlanType`,
                            `contractLengthInMonths`,
                            `monthlyAnnuityFactor`,
                            `initialFee`,
                            `notificationFee`,
                            `interestRatePercent`,
                            `numberOfInterestFreeMonths`,
                            `numberOfPaymentFreeMonths`,
                            `fromAmount`,
                            `toAmount`,
                            `timestamp`,
                            `countryCode`)
                            VALUES(";
                foreach ($param as $key => $value)
                    $q .= "'".$value."',";

                $q .= time().",";
                $q .= "'".$countryCode."'";
                $q .= ")";
                try{
                    $this->db->query($q);
                }catch (Exception $e) {
                    $this->log->write($e->getMessage());
                }

            }


    }
}
?>
