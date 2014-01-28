<?php

class ControllerTotalSveaFee extends Controller {

    private $error = array();

    public function index() {
        $this->load->language('total/svea_fee');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        //When Save btn is clicked
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->model_setting_setting->editSetting('svea_fee', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->redirect($this->getLink('extension/total'));
        }

        // localisation
        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_enabled'] = $this->language->get('text_enabled');
        $this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['text_none'] = $this->language->get('text_none');

        $this->data['button_save'] = $this->language->get('button_save');
        $this->data['button_cancel'] = $this->language->get('button_cancel');		
        $this->data['tab_general'] = $this->language->get('tab_general');
        
        // localisation of our config settings
        $this->data['version']  = floatval(VERSION);

        // create array of config cred(entials) for each country
        $cred = array();
        
        $cred[] = array(
            "lang" => "SE",
            "entry_total" => $this->config->get('entry_total_SE'),
            'entry_fee' => $this->config->get('entry_fee_SE'),
            'entry_tax_class' => $this->config->get('entry_tax_class_SE'),
            'entry_status' => $this->config->get('entry_status_SE'),
            'entry_sort_order' => $this->config->get('entry_sort_order_SE')       
        );
        $cred[] = array(
            "lang" => "NO",
            "entry_total" => $this->config->get('entry_total_NO'),
            'entry_fee' => $this->config->get('entry_fee_NO'),
            'entry_tax_class' => $this->config->get('entry_tax_class_NO'),
            'entry_status' => $this->config->get('entry_status_NO'),
            'entry_sort_order' => $this->config->get('entry_sort_order_NO')       
        );
        $cred[] = array(
            "lang" => "FI",
            "entry_total" => $this->config->get('entry_total_FI'),
            'entry_fee' => $this->config->get('entry_fee_FI'),
            'entry_tax_class' => $this->config->get('entry_tax_class_FI'),
            'entry_status' => $this->config->get('entry_status_FI'),
            'entry_sort_order' => $this->config->get('entry_sort_order_FI')       
        );        
        $cred[] = array(
            "lang" => "DK",
            "entry_total" => $this->config->get('entry_total_DK'),
            'entry_fee' => $this->config->get('entry_fee_DK'),
            'entry_tax_class' => $this->config->get('entry_tax_class_DK'),
            'entry_status' => $this->config->get('entry_status_DK'),
            'entry_sort_order' => $this->config->get('entry_sort_order_DK')       
        );        
        $cred[] = array(
            "lang" => "NL",
            "entry_total" => $this->config->get('entry_total_NL'),
            'entry_fee' => $this->config->get('entry_fee_NL'),
            'entry_tax_class' => $this->config->get('entry_tax_class_NL'),
            'entry_status' => $this->config->get('entry_status_NL'),
            'entry_sort_order' => $this->config->get('entry_sort_order_NL')       
        );        
        $cred[] = array(
            "lang" => "DE",
            "entry_total" => $this->config->get('entry_total_DE'),
            'entry_fee' => $this->config->get('entry_fee_DE'),
            'entry_tax_class' => $this->config->get('entry_tax_class_DE'),
            'entry_status' => $this->config->get('entry_status_DE'),
            'entry_sort_order' => $this->config->get('entry_sort_order_DE')       
        );        
     
        $this->data['credentials'] = $cred;
   
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->getLink('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_total'),
            'href' => $this->getLink('extension/total'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->getLink('total/svea_fee'),
            'separator' => ' :: '
        );

        $this->data['action'] = $this->getLink('total/svea_fee');
        $this->data['cancel'] = $this->getLink('extension/total');

        if (isset($this->request->post['svea_fee_total'])) {
            $this->data['svea_fee_total'] = $this->request->post['svea_fee_total'];
        } else {
            $this->data['svea_fee_total'] = $this->config->get('svea_fee_total');
        }


        if (isset($this->request->post['svea_fee_fee'])) {
            $this->data['svea_fee_fee'] = $this->request->post['svea_fee_fee'];
        } else {
            $this->data['svea_fee_fee'] = $this->config->get('svea_fee_fee');
        }

        if (isset($this->request->post['svea_fee_tax_class_id'])) {
            $this->data['svea_fee_tax_class_id'] = $this->request->post['svea_fee_tax_class_id'];
        } else {
            $this->data['svea_fee_tax_class_id'] = $this->config->get('svea_fee_tax_class_id');
        }

        if (isset($this->request->post['svea_fee_status'])) {
            $this->data['svea_fee_status'] = $this->request->post['svea_fee_status'];
        } else {
            $this->data['svea_fee_status'] = $this->config->get('svea_fee_status');
        }

        if (isset($this->request->post['svea_fee_sort_order'])) {
            $this->data['svea_fee_sort_order'] = $this->request->post['svea_fee_sort_order'];
        } else {
            $this->data['svea_fee_sort_order'] = $this->config->get('svea_fee_sort_order');
        }

        $this->load->model('localisation/tax_class');

        $this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->template = 'total/svea_fee.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render(TRUE));
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

    //The link function not avaliable in 1.4.x, therefore we need to implement the older way of setting links
    private function getLink($link) {
        return (floatval(VERSION) >= 1.5) ? $this->url->link($link, 'token=' . $this->session->data['token'], 'SSL') : HTTPS_SERVER . 'index.php?route=' . $link . '&token=' . $this->session->data['token'];
    }

}

?>