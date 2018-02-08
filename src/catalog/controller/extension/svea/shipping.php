<?php

class ControllerExtensionSveaShipping extends Controller
{

    public function index()
    {
        $email = (isset($this->request->post['email'])) ? $this->request->post['email'] : NULL;
        $postcode = (isset($this->request->post['postcode'])) ? $this->request->post['postcode'] : NULL;

        $this->session->data['module_sco_email'] = $email;
        $this->session->data['module_sco_postcode'] = $postcode;

        $address = array(
            'postcode' => $postcode,
            'country_id' => isset($this->session->data['module_sco_country_id']) ? strtoupper($this->session->data['module_sco_country_id']) : $this->config->get('config_country_id'),
            'zone_id' => $this->config->get('config_zone_id'),
            'iso_code_2' => isset($this->session->data['module_sco_country']) ? $this->session->data['module_sco_country'] : strtoupper($this->language->get('code')),
        );

        $json = array();
        $methods = array();

        $this->load->model('setting/extension');

        $results = $this->model_setting_extension->getExtensions('shipping');

        foreach ($results as $result) {
            if ($this->config->get('shipping_' .$result['code'] . '_status')) {
                $this->load->model('extension/shipping/' . $result['code']);
                $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($address);

                if ($quote) {
                    $methods[$result['code']] = array(
                        'title' => $quote['title'],
                        'quote' => $quote['quote'],
                        'sort_order' => $quote['sort_order'],
                        'error' => $quote['error']
                    );
                }
            }
        }

        $sort_order = array();

        foreach ($methods as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $methods);

        $this->session->data['shipping_methods'] = $methods;
        $this->session->data['shipping_method'] = (isset($this->session->data['shipping_method'])) ? $this->session->data['shipping_method'] : NULL;

        $json['methods'] = array();

        $text_multi = '%s - %s';
        $text_cost = '%s (+%s)';

        foreach ($methods as $method) {
            foreach ($method['quote'] as $quote) {

                $name = (count($method['quote']) > 0) ? sprintf($text_multi, $method['title'], $quote['title']) : $method['title'];
                $name = ($quote['cost']) ? sprintf($text_cost, $name, $quote['text']) : $name;

                $json['methods'][] = array(
                    'id' => $quote['code'],
                    'name' => $name,
                    'selected' => ($this->session->data['shipping_method']['code'] == $quote['code']) ? 1 : 0,
                );
            }
        }

        if (!count($json['methods'])) {

            $this->load->language('extension/svea/checkout');

            $json['methods'][] = array(
                'id' => '',
                'name' => $this->language->get('error_shipping'),
                'selected' => 0,
            );

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

    }

    public function save()
    {
        $json = array();

        if (isset($this->request->post['shipping'])) {
            $shipping = explode('.', $this->request->post['shipping']);

            $this->session->data['shipping_method'] =  false;
            if (isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
                $this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
