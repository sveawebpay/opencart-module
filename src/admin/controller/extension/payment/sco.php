<?php

class ControllerExtensionPaymentSco extends Controller
{
    public function index()
    {
        $this->load->language('extension/payment/sco');

        $data = array();

        $data = array_merge($data, $this->setLanguage());
        $data = array_merge($data, $this->setBreadcrumbs());

        // Set cancel url
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], true);

        // Load common controllers
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/sco.tpl', $data));
    }

    private function setBreadcrumbs()
    {
        $data = array();

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/sco', 'token=' . $this->session->data['token'], true)
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
