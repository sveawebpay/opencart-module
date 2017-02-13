<?php

class ControllerPaymentSco extends Controller
{
    private $name = 'sco';

    public function index()
    {
        // get language
        $this->load->language('payment/' . $this->name);

        $this->data = array();

        $this->data = array_merge($this->data, $this->setLanguage());
        $this->data = array_merge($this->data, $this->setBreadcrumbs());

        // Set cancel url
        $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        // Set template
        $this->template = 'payment/' . $this->name . '.tpl';

        // Load common controllers
        $this->children = array(
            'common/header',
            'common/footer'
        );

        // set response
        $this->response->setOutput($this->render());
    }

    private function setBreadcrumbs()
    {
        $data = array();

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/' . $this->name, 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        return $data;
    }

    private function setLanguage()
    {
        // Set title
        $data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($data['heading_title']);

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_no_settings'] = $this->language->get('text_no_settings');

        $data['button_cancel'] = $this->language->get('button_cancel');

        return $data;
    }
}
