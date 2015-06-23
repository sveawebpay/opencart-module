<?php
class ControllerPaymentsveacard extends Controller {
    private $error = array();
    protected $svea_version = '2.6.19';

    public function index() {
        $this->load->language('payment/svea_card');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

            //Remove whitespace  from input, outcommented cause do not filter correct
            $inputArray = array();
            foreach($this->request->post as $k => $i){
                $inputArray[$k] = $i;//($k == 'svea_card_sw_test' || 'svea_card_sw_prod') ? str_replace(" ","",$i) : $i;
            }

            //Save all settings
                $this->model_setting_setting->editSetting('svea_card', $inputArray);

                $this->session->data['success'] = $this->language->get('text_success');

                $this->redirect(HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token']);
        }

        $this->data['heading_title']      = $this->language->get('heading_title');
        $this->data['text_enabled']       = $this->language->get('text_enabled');
        $this->data['text_disabled']      = $this->language->get('text_disabled');
        $this->data['text_all_zones']     = $this->language->get('text_all_zones');

        $this->data['entry_order_status'] = $this->language->get('entry_order_status');
        $this->data['entry_status_order'] = $this->language->get('entry_status_order');
        $this->data['entry_status_canceled'] = $this->language->get('entry_status_canceled');
        $this->data['entry_status_canceled_text'] = $this->language->get('entry_status_canceled_text');
        $this->data['entry_status_confirmed'] = $this->language->get('entry_status_confirmed');
        $this->data['entry_status_confirmed_text'] = $this->language->get('entry_status_confirmed_text');
        $this->data['entry_status_refunded'] = $this->language->get('entry_status_refunded');
        $this->data['entry_status_refunded_text'] = $this->language->get('entry_status_refunded_text');
        $this->data['entry_geo_zone']     = $this->language->get('entry_geo_zone');
        $this->data['entry_status']       = $this->language->get('entry_status');
        $this->data['entry_sort_order']   = $this->language->get('entry_sort_order');
        $this->data['entry_payment_description']   = $this->language->get('entry_payment_description');

        $this->data['button_save']        = $this->language->get('button_save');
        $this->data['button_cancel']      = $this->language->get('button_cancel');

        $this->data['tab_general']        = $this->language->get('tab_general');
        $this->data['svea_version']         = $this->getSveaVersion();
        //Credentials
        $this->data['entry_test']        = $this->language->get('entry_test');
        $this->data['entry_prod']       = $this->language->get('entry_prod');

        //Definitions lang
        $this->data['entry_testmode']     = $this->language->get('entry_testmode');
        $this->data['entry_merchant_id']  = $this->language->get('entry_merchant_id');
        $this->data['entry_sw']           = $this->language->get('entry_sw');
        $this->data['entry_merchant_id']  = $this->language->get('entry_merchant_id');
        $this->data['entry_sw']           = $this->language->get('entry_sw');

        //Definitions settings
        $this->data['svea_card_sort_order']  = $this->config->get('svea_card_sort_order');
        $this->data['svea_card_testmode']    = $this->config->get('svea_card_testmode');

        $this->data['version']  = floatval(VERSION);

        $this->data['value_merchant_test'] = $this->config->get('svea_card_merchant_id_test');
        $this->data['value_sw_test'] = $this->config->get('svea_card_sw_test');
        $this->data['value_merchant_prod'] = $this->config->get('svea_card_merchant_id_prod');
        $this->data['value_sw_prod'] = $this->config->get('svea_card_sw_prod');

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
        'href'      => HTTPS_SERVER . 'index.php?route=payment/svea_card&token=' . $this->session->data['token'],
        'text'      => $this->language->get('heading_title'),
        'separator' => ' :: '
        );

        $this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/svea_card&token=' . $this->session->data['token'];

        $this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];

        //statuses
        $this->load->model('localisation/order_status');
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        if (isset($this->request->post['svea_card_order_status_id'])) {
                $this->data['svea_card_order_status_id'] = $this->request->post['svea_card_order_status_id'];
        } else {
                $this->data['svea_card_order_status_id'] = $this->config->get('svea_card_order_status_id');
        }
        if (isset($this->request->post['svea_card_canceled_status_id'])) {
            $this->data['svea_card_canceled_status_id'] = $this->request->post['svea_card_canceled_status_id'];
        } else {
            $this->data['svea_card_canceled_status_id'] = $this->config->get('svea_card_canceled_status_id');
        }
        if (isset($this->request->post['svea_card_deliver_status_id'])) {
            $this->data['svea_card_deliver_status_id'] = $this->request->post['svea_card_deliver_status_id'];
        } else {
            $this->data['svea_card_deliver_status_id'] = $this->config->get('svea_card_deliver_status_id');
        }
        if (isset($this->request->post['svea_card_refunded_status_id'])) {
            $this->data['svea_card_refunded_status_id'] = $this->request->post['svea_card_refunded_status_id'];
        } else {
            $this->data['svea_card_refunded_status_id'] = $this->config->get('svea_card_refunded_status_id');
        }

        if (isset($this->request->post['svea_card_geo_zone_id'])) {
                $this->data['svea_card_geo_zone_id'] = $this->request->post['svea_card_geo_zone_id'];
        } else {
                $this->data['svea_card_geo_zone_id'] = $this->config->get('svea_card_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['svea_card_status'])) {
                $this->data['svea_card_status'] = $this->request->post['svea_card_status'];
        } else {
                $this->data['svea_card_status'] = $this->config->get('svea_card_status');
        }

        if (isset($this->request->post['svea_card_sort_order'])) {
                $this->data['svea_card_sort_order'] = $this->request->post['svea_card_sort_order'];
        } else {
                $this->data['svea_card_sort_order'] = $this->config->get('svea_card_sort_order');
        }
                //payment info
        if (isset($this->request->post['svea_card_payment_description'])) {
                $this->data['svea_card_payment_description'] = $this->request->post['svea_card_payment_description'];
        } else {
                $this->data['svea_card_payment_description'] = $this->config->get('svea_card_payment_description');
        }

        if (isset($this->request->post['svea_card_testmode'])) {
			$this->data['svea_card_testmode'] = $this->request->post['svea_card_testmode'];
            } else {
                    $this->data['svea_card_testmode'] = $this->config->get('svea_card_testmode');
            }

            $this->template = 'payment/svea_card.tpl';
            $this->children = array(
                    'common/header',
                    'common/footer'
            );
            $this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}

        protected function getSveaVersion(){
            $update_url = "https://github.com/sveawebpay/opencart-module/archive/Opencart_1.x.zip";
            $docs_url = "https://github.com/sveawebpay/opencart-module/tree/Opencart_1.x";
            $url = "https://raw.githubusercontent.com/sveawebpay/opencart-module/Opencart_1.x/docs/info.json";
            $json = file_get_contents($url);
            $data = json_decode($json);
            if($data->module_version == $this->svea_version){
                return "You have the latest ". $this->svea_version . " version.";
            }else{
             return $this->svea_version . '<br />
                There is a new version available.<br />
                <a href="'.$docs_url.'" title="Go to release notes on github">View version details</a> or <br />
                <a title="Download zip" href="'.$update_url.'"><img width="67" src="view/image/svea_download.png"></a>';
            }
        }

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/svea_card')) {
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