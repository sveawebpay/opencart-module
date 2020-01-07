<?php

class ControllerExtensionTotalSveaFee extends Controller
{
    protected $total_svea_version;
    private $error = array();

    private $userTokenString = "user_";
    private $extensionString = "setting/extension";
    private $totalString = "total_";
    private $linkString = "marketplace/extension";

    public function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->userTokenString = "";
            $this->extensionString = "extension/extension";
            $this->totalString = "";
            $this->linkString = "extension/extension";
        }
    }

    public function index()
    {
        $this->total_svea_version = $this->getModuleVersion();

        $this->load->language('extension/total/svea_fee');

        $this->setVersionStrings();
        
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

            // if fee enabled in any country, set it as enabled in the order_total listing
            $total_svea_fee_status = ($this->request->post[$this->totalString . 'svea_fee_status_SE'] ||
                $this->request->post[$this->totalString . 'svea_fee_status_NO'] ||
                $this->request->post[$this->totalString . 'svea_fee_status_DK'] ||
                $this->request->post[$this->totalString . 'svea_fee_status_FI'] ||
                $this->request->post[$this->totalString . 'svea_fee_status_NL'] ||
                $this->request->post[$this->totalString . 'svea_fee_status_DE'])
                ? true : false;
            // stores config settings to db
            $this->model_setting_setting->editSetting($this->totalString . 'svea_fee', array_merge($this->request->post, array($this->totalString . 'svea_fee_status' => $total_svea_fee_status)));
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=total', 'SSL'));
        }

        $data[$this->totalString . 'svea_version'] = $this->getSveaVersion();
        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_none'] = $this->language->get('text_none');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['tab_general'] = $this->language->get('tab_general');
        $data['version'] = floatval(VERSION);

        // localisation of our config settings names
        $data['entry_fee'] = $this->language->get('entry_fee');
        $data['entry_tax_class'] = $this->language->get('entry_tax_class');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        // create & fill array of config credentials for each country
        $cred = array();

        $cred[] = array(
            "lang" => "SE",
            // (tpl "name" will be built from this key + "lang" above) => tpl "value"
            $this->totalString . 'svea_fee_fee' => $this->config->get($this->totalString . 'svea_fee_fee_SE'),
            $this->totalString . 'svea_fee_tax_class' => $this->config->get($this->totalString . 'svea_fee_tax_class_SE'),
            $this->totalString . 'svea_fee_status' => $this->config->get($this->totalString . 'svea_fee_status_SE'),
            $this->totalString . 'svea_fee_sort_order' => $this->config->get($this->totalString . 'svea_fee_sort_order_SE')
        );
        $cred[] = array(
            "lang" => "NO",
            $this->totalString . 'svea_fee_fee' => $this->config->get($this->totalString . 'svea_fee_fee_NO'),
            $this->totalString . 'svea_fee_tax_class' => $this->config->get($this->totalString . 'svea_fee_tax_class_NO'),
            $this->totalString . 'svea_fee_status' => $this->config->get($this->totalString . 'svea_fee_status_NO'),
            $this->totalString . 'svea_fee_sort_order' => $this->config->get($this->totalString . 'svea_fee_sort_order_NO')
        );
        $cred[] = array(
            "lang" => "FI",
            $this->totalString . 'svea_fee_fee' => $this->config->get($this->totalString . 'svea_fee_fee_FI'),
            $this->totalString . 'svea_fee_tax_class' => $this->config->get($this->totalString . 'svea_fee_tax_class_FI'),
            $this->totalString . 'svea_fee_status' => $this->config->get($this->totalString . 'svea_fee_status_FI'),
            $this->totalString . 'svea_fee_sort_order' => $this->config->get($this->totalString . 'svea_fee_sort_order_FI')
        );
        $cred[] = array(
            "lang" => "DK",
            $this->totalString . 'svea_fee_fee' => $this->config->get($this->totalString . 'svea_fee_fee_DK'),
            $this->totalString . 'svea_fee_tax_class' => $this->config->get($this->totalString . 'svea_fee_tax_class_DK'),
            $this->totalString . 'svea_fee_status' => $this->config->get($this->totalString . 'svea_fee_status_DK'),
            $this->totalString . 'svea_fee_sort_order' => $this->config->get($this->totalString . 'svea_fee_sort_order_DK')
        );
        $cred[] = array(
            "lang" => "NL",
            $this->totalString . 'svea_fee_fee' => $this->config->get($this->totalString . 'svea_fee_fee_NL'),
            $this->totalString . 'svea_fee_tax_class' => $this->config->get($this->totalString . 'svea_fee_tax_class_NL'),
            $this->totalString . 'svea_fee_status' => $this->config->get($this->totalString . 'svea_fee_status_NL'),
            $this->totalString . 'svea_fee_sort_order' => $this->config->get($this->totalString . 'svea_fee_sort_order_NL')
        );
        $cred[] = array(
            "lang" => "DE",
            $this->totalString . 'svea_fee_fee' => $this->config->get($this->totalString . 'svea_fee_fee_DE'),
            $this->totalString . 'svea_fee_tax_class' => $this->config->get($this->totalString . 'svea_fee_tax_class_DE'),
            $this->totalString . 'svea_fee_status' => $this->config->get($this->totalString . 'svea_fee_status_DE'),
            $this->totalString . 'svea_fee_sort_order' => $this->config->get($this->totalString . 'svea_fee_sort_order_DE')
        );

        $data['credentials'] = $cred;

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_total'),
            'href' => $this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=total', true),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/total/svea_fee', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true),
            'separator' => ' :: '
        );

        // actions urls for the save/cancel buttons
        $data['action'] = $this->url->link('extension/total/svea_fee', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true);
        $data['cancel'] = $this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=total', true);


        $this->load->model('localisation/tax_class');

        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/total/svea_fee', $data));
    }

    /**
     * iff this returns true, write the (new) settings to database
     * @return boolean
     */
    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/total/svea_fee')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function getSveaVersion()
    {
        $update_url = "https://github.com/sveawebpay/opencart-module/archive/master.zip";
        $docs_url = "https://github.com/sveawebpay/opencart-module/releases";
        $url = "https://raw.githubusercontent.com/sveawebpay/opencart-module/master/src/svea/version.json";
        $json = file_get_contents($url);
        $data = json_decode($json);

        if ($data->version <= $this->total_svea_version) {
            return $this->total_svea_version . "<br />
            You have the latest version.";
        } else {
            return $this->total_svea_version . '<br />
                There is a new version available.<br />
                <a href="' . $docs_url . '" title="Go to release notes on github">View version details</a> or <br />
                <a title="Download zip" href="' . $update_url . '"><img width="67" src="view/image/download.png"></a>';

        }

    }

    protected function getModuleVersion()
    {
        $jsonData = json_decode(file_get_contents(DIR_APPLICATION . '../svea/version.json'), true);
        return $jsonData['version'];
    }
}
