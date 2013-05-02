<?php
class ControllerPaymentsveadirectbank extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/svea_directbank');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
		  
            //Remove whitespace  from input
            $inputArray = array();
            foreach($this->request->post as $k => $i){
                $inputArray[$k] = str_replace(" ","",$i);
            }
            
            //Save all settings
			$this->model_setting_setting->editSetting('svea_directbank', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect(HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token']);
		}

		$this->data['heading_title']      = $this->language->get('heading_title');
		$this->data['text_enabled']       = $this->language->get('text_enabled');
		$this->data['text_disabled']      = $this->language->get('text_disabled');
		$this->data['text_all_zones']     = $this->language->get('text_all_zones');

		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_geo_zone']     = $this->language->get('entry_geo_zone');
		$this->data['entry_status']       = $this->language->get('entry_status');
		$this->data['entry_sort_order']   = $this->language->get('entry_sort_order');

		$this->data['button_save']        = $this->language->get('button_save');
		$this->data['button_cancel']      = $this->language->get('button_cancel');

		$this->data['tab_general']        = $this->language->get('tab_general');

         //Credentials
        $this->data['entry_test']        = $this->language->get('entry_test');
        $this->data['entry_prod']       = $this->language->get('entry_prod');

        //Definitions lang
        $this->data['entry_merchant_id']  = $this->language->get('entry_merchant_id');
        $this->data['entry_testmode']     = $this->language->get('entry_testmode');
        $this->data['entry_sw']           = $this->language->get('entry_sw');

        //Definitions settings
        $this->data['svea_directbank_sort_order']  = $this->config->get('svea_directbank_sort_order');
        $this->data['svea_directbank_testmode']    = $this->config->get('svea_directbank_testmode');

           $this->data['version']             = floatval(VERSION);
        //test tabs
        $lang_test = array();
        $lang_test[] = array("lang" => "SE","value_merchant" => $this->config->get('svea_directbank_merchant_id_test_SE'),"name_merchant" => 'svea_directbank_merchant_id_test_SE',"value_sw" => $this->config->get('svea_directbank_sw_test_SE'),"name_sw" => 'svea_directbank_sw_test_SE');
        $lang_test[] = array("lang" => "NO","value_merchant" => $this->config->get('svea_directbank_merchant_id_test_NO'),"name_merchant" => 'svea_directbank_merchant_id_test_NO',"value_sw" => $this->config->get('svea_directbank_sw_test_NO'),"name_sw" => 'svea_directbank_sw_test_NO');
        $lang_test[] = array("lang" => "FI","value_merchant" => $this->config->get('svea_directbank_merchant_id_test_FI'),"name_merchant" => 'svea_directbank_merchant_id_test_FI',"value_sw" => $this->config->get('svea_directbank_sw_test_FI'),"name_sw" => 'svea_directbank_sw_test_FI');
        $lang_test[] = array("lang" => "DK","value_merchant" => $this->config->get('svea_directbank_merchant_id_test_DK'),"name_merchant" => 'svea_directbank_merchant_id_test_DK',"value_sw" => $this->config->get('svea_directbank_sw_test_DK'),"name_sw" => 'svea_directbank_sw_test_DK');
        $lang_test[] = array("lang" => "NL","value_merchant" => $this->config->get('svea_directbank_merchant_id_test_NL'),"name_merchant" => 'svea_directbank_merchant_id_test_NL',"value_sw" => $this->config->get('svea_directbank_sw_test_NL'),"name_sw" => 'svea_directbank_sw_test_NL');
        $lang_test[] = array("lang" => "DE","value_merchant" => $this->config->get('svea_directbank_merchant_id_test_DE'),"name_merchant" => 'svea_directbank_merchant_id_test_DE',"value_sw" => $this->config->get('svea_directbank_sw_test_DE'),"name_sw" => 'svea_directbank_sw_test_DE');

        //prod tabs
        $lang_prod = array();
        $lang_prod[] = array("lang" => "SE","value_merchant" => $this->config->get('svea_directbank_merchant_id_prod_SE'),"name_merchant" => 'svea_directbank_merchant_id_prod_SE',"value_sw" => $this->config->get('svea_directbank_sw_prod_SE'),"name_sw" => 'svea_directbank_sw_prod_SE');
        $lang_prod[] = array("lang" => "NO","value_merchant" => $this->config->get('svea_directbank_merchant_id_prod_NO'),"name_merchant" => 'svea_directbank_merchant_id_prod_NO',"value_sw" => $this->config->get('svea_directbank_sw_prod_NO'),"name_sw" => 'svea_directbank_sw_prod_NO');
        $lang_prod[] = array("lang" => "FI","value_merchant" => $this->config->get('svea_directbank_merchant_id_prod_FI'),"name_merchant" => 'svea_directbank_merchant_id_prod_FI',"value_sw" => $this->config->get('svea_directbank_sw_prod_FI'),"name_sw" => 'svea_directbank_sw_prod_FI');
        $lang_prod[] = array("lang" => "DK","value_merchant" => $this->config->get('svea_directbank_merchant_id_prod_DK'),"name_merchant" => 'svea_directbank_merchant_id_prod_DK',"value_sw" => $this->config->get('svea_directbank_sw_prod_DK'),"name_sw" => 'svea_directbank_sw_prod_DK');
        $lang_prod[] = array("lang" => "NL","value_merchant" => $this->config->get('svea_directbank_merchant_id_prod_NL'),"name_merchant" => 'svea_directbank_merchant_id_prod_NL',"value_sw" => $this->config->get('svea_directbank_sw_prod_NL'),"name_sw" => 'svea_directbank_sw_prod_NL');
        $lang_prod[] = array("lang" => "DE","value_merchant" => $this->config->get('svea_directbank_merchant_id_prod_DE'),"name_merchant" => 'svea_directbank_merchant_id_prod_DE',"value_sw" => $this->config->get('svea_directbank_sw_prod_DE'),"name_sw" => 'svea_directbank_sw_prod_DE');

        $this->data['prod'] = $lang_prod;
        $this->data['test'] = $lang_test;

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
       		'href'      => HTTPS_SERVER . 'index.php?route=payment/svea_directbank&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/svea_directbank&token=' . $this->session->data['token'];

		$this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];

		if (isset($this->request->post['svea_directbank_order_status_id'])) {
			$this->data['svea_directbank_order_status_id'] = $this->request->post['svea_directbank_order_status_id'];
		} else {
			$this->data['svea_directbank_order_status_id'] = $this->config->get('svea_directbank_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['svea_directbank_geo_zone_id'])) {
			$this->data['svea_directbank_geo_zone_id'] = $this->request->post['svea_directbank_geo_zone_id'];
		} else {
			$this->data['svea_directbank_geo_zone_id'] = $this->config->get('svea_directbank_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['svea_directbank_status'])) {
			$this->data['svea_directbank_status'] = $this->request->post['svea_directbank_status'];
		} else {
			$this->data['svea_directbank_status'] = $this->config->get('svea_directbank_status');
		}

		if (isset($this->request->post['svea_directbank_sort_order'])) {
			$this->data['svea_directbank_sort_order'] = $this->request->post['svea_directbank_sort_order'];
		} else {
			$this->data['svea_directbank_sort_order'] = $this->config->get('svea_directbank_sort_order');
		}

        if (isset($this->request->post['svea_directbank_testmode'])) {
			$this->data['svea_directbank_testmode'] = $this->request->post['svea_directbank_testmode'];
		} else {
			$this->data['svea_directbank_testmode'] = $this->config->get('svea_directbank_testmode');
		}

		$this->template = 'payment/svea_directbank.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/svea_directbank')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
?>