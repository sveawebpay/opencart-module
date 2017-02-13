<?php
class ControllerTotalSveaFee extends Controller {
    private $error = array();
    protected $svea_version = '3.1.4';

    public function index() {
        $this->load->language('total/svea_fee');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

            // if fee enabled in any country, set it as enabled in the order_total listing
            $svea_fee_status = ($this->request->post['svea_fee_status_SE'] ||
                $this->request->post['svea_fee_status_NO'] ||
                $this->request->post['svea_fee_status_DK'] ||
                $this->request->post['svea_fee_status_FI'] ||
                $this->request->post['svea_fee_status_NL'] ||
                $this->request->post['svea_fee_status_DE'] )
                ? true : false;
            // stores config settings to db
            $this->model_setting_setting->editSetting('svea_fee', array_merge($this->request->post, array('svea_fee_status' => $svea_fee_status)));
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data['svea_version'] = $this->getSveaVersion();
        // localisation of i.e. virtuemart gui elements
        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_none'] = $this->language->get('text_none');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['tab_general'] = $this->language->get('tab_general');
        $data['version']  = floatval(VERSION);

        // localisation of our config settings names
        $data['entry_fee'] = $this->language->get('entry_fee');
        $data['entry_tax_class'] = $this->language->get('entry_tax_class');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        // create & fill array of config cred(entials) for each country
        $cred = array();

        $cred[] = array(
            "lang" => "SE",
            // (tpl "name" will be built from this key + "lang" above) => tpl "value"
            'svea_fee_fee' => $this->config->get('svea_fee_fee_SE'),
            'svea_fee_tax_class' => $this->config->get('svea_fee_tax_class_SE'),
            'svea_fee_status' => $this->config->get('svea_fee_status_SE'),
            'svea_fee_sort_order' => $this->config->get('svea_fee_sort_order_SE')
        );
        $cred[] = array(
            "lang" => "NO",
            'svea_fee_fee' => $this->config->get('svea_fee_fee_NO'),
            'svea_fee_tax_class' => $this->config->get('svea_fee_tax_class_NO'),
            'svea_fee_status' => $this->config->get('svea_fee_status_NO'),
            'svea_fee_sort_order' => $this->config->get('svea_fee_sort_order_NO')
        );
        $cred[] = array(
            "lang" => "FI",
            'svea_fee_fee' => $this->config->get('svea_fee_fee_FI'),
            'svea_fee_tax_class' => $this->config->get('svea_fee_tax_class_FI'),
            'svea_fee_status' => $this->config->get('svea_fee_status_FI'),
            'svea_fee_sort_order' => $this->config->get('svea_fee_sort_order_FI')
        );
        $cred[] = array(
            "lang" => "DK",
            'svea_fee_fee' => $this->config->get('svea_fee_fee_DK'),
            'svea_fee_tax_class' => $this->config->get('svea_fee_tax_class_DK'),
            'svea_fee_status' => $this->config->get('svea_fee_status_DK'),
            'svea_fee_sort_order' => $this->config->get('svea_fee_sort_order_DK')
        );
        $cred[] = array(
            "lang" => "NL",
            'svea_fee_fee' => $this->config->get('svea_fee_fee_NL'),
            'svea_fee_tax_class' => $this->config->get('svea_fee_tax_class_NL'),
            'svea_fee_status' => $this->config->get('svea_fee_status_NL'),
            'svea_fee_sort_order' => $this->config->get('svea_fee_sort_order_NL')
        );
        $cred[] = array(
            "lang" => "DE",
            'svea_fee_fee' => $this->config->get('svea_fee_fee_DE'),
            'svea_fee_tax_class' => $this->config->get('svea_fee_tax_class_DE'),
            'svea_fee_status' => $this->config->get('svea_fee_status_DE'),
            'svea_fee_sort_order' => $this->config->get('svea_fee_sort_order_DE')
        );

        $data['credentials'] = $cred;

        /*Sort order*/
        if (isset($this->request->post['svea_fee_sort_order'])) {
            $data['svea_fee_sort_order'] = $this->request->post['svea_fee_sort_order'];
        } else {
            $data['svea_fee_sort_order'] = $this->config->get('svea_fee_sort_order');
        }

        // pass any virtuemart error to template
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        // pass virtuemart breadcrumbs to template
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->getLink('common/home'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_total'),
            'href' => $this->getLink('extension/total'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->getLink('total/svea_fee'),
            'separator' => ' :: '
        );

        // actions urls for the save/cancel buttons
        $data['action'] = $this->getLink('total/svea_fee');
        $data['cancel'] = $this->getLink('extension/total');

        $this->load->model('localisation/tax_class');

        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('total/svea_fee.tpl', $data));
    }

    /**
     * iff this returns true, write the (new) settings to database
     * @return boolean
     */
    private function validate() {
        if (!$this->user->hasPermission('modify', 'total/svea_fee')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
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

    //The link function not avaliable in 1.4.x, therefore we need to implement the older way of setting links
    private function getLink($link) {
        return (floatval(VERSION) >= 1.5) ?
            $this->url->link($link, 'token=' . $this->session->data['token'], 'SSL') :
            HTTPS_SERVER . 'index.php?route=' . $link . '&token=' . $this->session->data['token'];
    }
}

?>