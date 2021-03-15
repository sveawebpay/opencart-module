<?php

class ControllerExtensionPaymentSco extends Controller
{
    private $userTokenString = "user_";
    private $linkString = "marketplace/extension";
    private $paymentString ="payment_";
    private $moduleString = "module_";
    private $appendString = "_before";

    public function setVersionStrings()
    {
        if (VERSION < 3.0) {
            $this->userTokenString = "";
            $this->linkString = "extension/extension";
            $this->paymentString = "";
            $this->moduleString = "";
            $this->appendString = "";
        }
    }

    public function index()
    {
        $this->setVersionStrings();
        $this->load->language('extension/payment/sco');

        $data = array();

        $data = array_merge($data, $this->setLanguage());
        $data = array_merge($data, $this->setBreadcrumbs());

        // Set cancel url
        $data['cancel'] = $this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=payment', true);

        // Load common controllers
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['version'] = VERSION;

        $this->response->setOutput($this->load->view('extension/payment/sco', $data));
    }

    private function setBreadcrumbs()
    {
        $data = array();

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/svea_directbank', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true)
        );

        return $data;
    }

    private function setLanguage()
    {
        $data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($data['heading_title']);

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_no_settings'] = $this->language->get('text_no_settings');

        $data['button_cancel'] = $this->language->get('button_cancel');

        return $data;
    }
}
