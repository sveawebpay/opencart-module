<?php
class ControllerPaymentsveadirectbank extends Controller {
	private $error = array();
        protected $svea_version = '3.0.11';

	public function index() {
		$this->load->language('payment/svea_directbank');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

            //Remove whitespace  from input
            $inputArray = array();
            foreach($this->request->post as $k => $i){
                 $inputArray[$k] = ($k == 'svea_directbank_sw_test' || 'svea_directbank_sw_prod') ? str_replace(" ","",$i) : $i;
            }

            //Save all settings
			$this->model_setting_setting->editSetting('svea_directbank', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			 $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

                $data['svea_version'] = $this->getSveaVersion();
		$data['heading_title']      = $this->language->get('heading_title');
		$data['text_enabled']       = $this->language->get('text_enabled');
		$data['text_disabled']      = $this->language->get('text_disabled');
		$data['text_all_zones']     = $this->language->get('text_all_zones');

		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_status_order'] = $this->language->get('entry_status_order');
                $data['entry_status_refunded'] = $this->language->get('entry_status_refunded');
                $data['entry_status_refunded_text'] = $this->language->get('entry_status_refunded_text');
		$data['entry_geo_zone']     = $this->language->get('entry_geo_zone');
		$data['entry_status']       = $this->language->get('entry_status');
		$data['entry_sort_order']   = $this->language->get('entry_sort_order');
                $data['entry_payment_description']   = $this->language->get('entry_payment_description');
		$data['button_save']        = $this->language->get('button_save');
		$data['button_cancel']      = $this->language->get('button_cancel');

		$data['tab_general']        = $this->language->get('tab_general');

         //Credentials
        $data['entry_test']        = $this->language->get('entry_test');
        $data['entry_prod']       = $this->language->get('entry_prod');

        //Definitions lang
        $data['entry_merchant_id']  = $this->language->get('entry_merchant_id');
        $data['entry_testmode']     = $this->language->get('entry_testmode');
        $data['entry_sw']           = $this->language->get('entry_sw');

        //Definitions settings
        $data['svea_directbank_sort_order']  = $this->config->get('svea_directbank_sort_order');
        $data['svea_directbank_testmode']    = $this->config->get('svea_directbank_testmode');

        $data['version']             = floatval(VERSION);

        $data['value_merchant_test'] = $this->config->get('svea_directbank_merchant_id_test');
        $data['value_sw_test'] = $this->config->get('svea_directbank_sw_test');
        $data['value_merchant_prod'] = $this->config->get('svea_directbank_merchant_id_prod');
        $data['value_sw_prod'] = $this->config->get('svea_directbank_sw_prod');

        if (isset($this->error['warning'])) {
                $data['error_warning'] = $this->error['warning'];
        } else {
                $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_payment'),
                'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('payment/svea_directbank', 'token=' . $this->session->data['token'], 'SSL')
        );
        $data['action'] = $this->url->link('payment/svea_directbank', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

      //statuses
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        if (isset($this->request->post['svea_directbank_order_status_id'])) {
                $data['svea_directbank_order_status_id'] = $this->request->post['svea_directbank_order_status_id'];
        } else {
                $data['svea_directbank_order_status_id'] = $this->config->get('svea_directbank_order_status_id');
        }
        if (isset($this->request->post['svea_directbank_refunded_status_id'])) {
                $data['svea_directbank_refunded_status_id'] = $this->request->post['svea_directbank_refunded_status_id'];
        } else {
                $data['svea_directbank_refunded_status_id'] = $this->config->get('svea_directbank_refunded_status_id');
        }

        if (isset($this->request->post['svea_directbank_geo_zone_id'])) {
                $data['svea_directbank_geo_zone_id'] = $this->request->post['svea_directbank_geo_zone_id'];
        } else {
                $data['svea_directbank_geo_zone_id'] = $this->config->get('svea_directbank_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['svea_directbank_status'])) {
                $data['svea_directbank_status'] = $this->request->post['svea_directbank_status'];
        } else {
                $data['svea_directbank_status'] = $this->config->get('svea_directbank_status');
        }

        if (isset($this->request->post['svea_directbank_sort_order'])) {
                $data['svea_directbank_sort_order'] = $this->request->post['svea_directbank_sort_order'];
        } else {
                $data['svea_directbank_sort_order'] = $this->config->get('svea_directbank_sort_order');
        }
        //payment info
        if (isset($this->request->post['svea_directbank_payment_description'])) {
                $data['svea_directbank_payment_description'] = $this->request->post['svea_directbank_payment_description'];
        } else {
                $data['svea_directbank_payment_description'] = $this->config->get('svea_directbank_payment_description');
        }

        if (isset($this->request->post['svea_directbank_testmode'])) {
			$data['svea_directbank_testmode'] = $this->request->post['svea_directbank_testmode'];
		} else {
			$data['svea_directbank_testmode'] = $this->config->get('svea_directbank_testmode');
		}

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/svea_directbank.tpl', $data));
	}

          public function install() {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('svea_directbank', array('svea_directbank_status'=>1));
	}

	public function uninstall() {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('svea_directbank', array('svea_directbank_status'=>0));
	}

         protected function getSveaVersion(){
            $update_url = "https://github.com/sveawebpay/opencart-module/archive/master.zip";
            $docs_url = "https://github.com/sveawebpay/opencart-module/releases";
            $url = "https://raw.githubusercontent.com/sveawebpay/opencart-module/master/docs/info.json";
            $json = file_get_contents($url);
            $data = json_decode($json);

            if($data->module_version == $this->svea_version){
                return "You have the latest ". $this->svea_version . " version.";
            }else{
             return $this->svea_version . '<br />
                There is a new version available.<br />
                <a href="'.$docs_url.'" title="Go to release notes on github">View version details</a> or <br />
                <a title="Download zip" href="'.$update_url.'"><img width="67" src="view/image/download.png"></a>';

            }

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