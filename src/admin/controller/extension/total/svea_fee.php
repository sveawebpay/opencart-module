<?php

class ControllerExtensionTotalSveaFee extends Controller
{
    protected $total_svea_version = '4.3.3';
    private $error = array();

    public function index()
    {
        $this->load->language('extension/total/svea_fee');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

            // if fee enabled in any country, set it as enabled in the order_total listing
            $total_svea_fee_status = ($this->request->post['total_svea_fee_status_SE'] ||
                $this->request->post['total_svea_fee_status_NO'] ||
                $this->request->post['total_svea_fee_status_DK'] ||
                $this->request->post['total_svea_fee_status_FI'] ||
                $this->request->post['total_svea_fee_status_NL'] ||
                $this->request->post['total_svea_fee_status_DE'])
                ? true : false;
            // stores config settings to db
            $this->model_setting_setting->editSetting('total_svea_fee', array_merge($this->request->post, array('total_svea_fee_status' => $total_svea_fee_status)));
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', 'SSL'));
        }

        $data['total_svea_version'] = $this->getSveaVersion();
        // localisation of i.e. virtuemart gui elements
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

        // create & fill array of config cred(entials) for each country
        $cred = array();

        $cred[] = array(
            "lang" => "SE",
            // (tpl "name" will be built from this key + "lang" above) => tpl "value"
            'total_svea_fee_fee' => $this->config->get('total_svea_fee_fee_SE'),
            'total_svea_fee_tax_class' => $this->config->get('total_svea_fee_tax_class_SE'),
            'total_svea_fee_status' => $this->config->get('total_svea_fee_status_SE'),
            'total_svea_fee_sort_order' => $this->config->get('total_svea_fee_sort_order_SE')
        );
        $cred[] = array(
            "lang" => "NO",
            'total_svea_fee_fee' => $this->config->get('total_svea_fee_fee_NO'),
            'total_svea_fee_tax_class' => $this->config->get('total_svea_fee_tax_class_NO'),
            'total_svea_fee_status' => $this->config->get('total_svea_fee_status_NO'),
            'total_svea_fee_sort_order' => $this->config->get('total_svea_fee_sort_order_NO')
        );
        $cred[] = array(
            "lang" => "FI",
            'total_svea_fee_fee' => $this->config->get('total_svea_fee_fee_FI'),
            'total_svea_fee_tax_class' => $this->config->get('total_svea_fee_tax_class_FI'),
            'total_svea_fee_status' => $this->config->get('total_svea_fee_status_FI'),
            'total_svea_fee_sort_order' => $this->config->get('total_svea_fee_sort_order_FI')
        );
        $cred[] = array(
            "lang" => "DK",
            'total_svea_fee_fee' => $this->config->get('total_svea_fee_fee_DK'),
            'total_svea_fee_tax_class' => $this->config->get('total_svea_fee_tax_class_DK'),
            'total_svea_fee_status' => $this->config->get('total_svea_fee_status_DK'),
            'total_svea_fee_sort_order' => $this->config->get('total_svea_fee_sort_order_DK')
        );
        $cred[] = array(
            "lang" => "NL",
            'total_svea_fee_fee' => $this->config->get('total_svea_fee_fee_NL'),
            'total_svea_fee_tax_class' => $this->config->get('total_svea_fee_tax_class_NL'),
            'total_svea_fee_status' => $this->config->get('total_svea_fee_status_NL'),
            'total_svea_fee_sort_order' => $this->config->get('total_svea_fee_sort_order_NL')
        );
        $cred[] = array(
            "lang" => "DE",
            'total_svea_fee_fee' => $this->config->get('total_svea_fee_fee_DE'),
            'total_svea_fee_tax_class' => $this->config->get('total_svea_fee_tax_class_DE'),
            'total_svea_fee_status' => $this->config->get('total_svea_fee_status_DE'),
            'total_svea_fee_sort_order' => $this->config->get('total_svea_fee_sort_order_DE')
        );

        $data['credentials'] = $cred;

        /*Sort order*/
        if (isset($this->request->post['total_svea_fee_sort_order'])) {
            $data['total_svea_fee_sort_order'] = $this->request->post['total_svea_fee_sort_order'];
        } else {
            $data['total_svea_fee_sort_order'] = $this->config->get('total_svea_fee_sort_order');
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
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_total'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/total/svea_fee', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => ' :: '
        );

        // actions urls for the save/cancel buttons
        $data['action'] = $this->url->link('extension/total/svea_fee', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true);


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
        $url = "https://raw.githubusercontent.com/sveawebpay/opencart-module/master/docs/info.json";
        $json = file_get_contents($url);
        $data = json_decode($json);

        if ($data->module_version == $this->total_svea_version) {
            return "You have the latest " . $this->total_svea_version . " version.";
        } else {
            return $this->total_svea_version . '<br />
                There is a new version available.<br />
                <a href="' . $docs_url . '" title="Go to release notes on github">View version details</a> or <br />
                <a title="Download zip" href="' . $update_url . '"><img width="67" src="view/image/download.png"></a>';

        }

    }

    //The link function not avaliable in 1.4.x, therefore we need to implement the older way of setting links
    private function getLink($link)
    {
        return $this->url->link($link, 'user_token=' . $this->session->data['user_token'] . '&type=total', 'SSL');
    }
}
