<?php

class ControllerSveaCart extends Controller
{

    public function index()
    {
        $products = $this->cart->getProducts();

        $this->load->language('svea/checkout');
        $this->load->model('setting/extension');

        $this->data = array();
        $this->data['cart'] = $this->url->link('checkout/cart');
        $this->data['text_change_cart'] = $this->language->get('text_change_cart');

        // PRODUCTS
        $this->data['products'] = array();

        foreach ($products as $product) {

            $product['price'] = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            $product['price'] = $this->removeTrail($product['price']);

            $this->data['products'][] = array(
                'product_id' => $product['product_id'],
                'model' => $product['model'],
                'name' => $product['name'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'option' => $product['option'],
            );
        }

        // VOUCHERS
        $this->data['vouchers'] = array();

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $key => $voucher) {

                $voucher['amount'] = $this->currency->format($voucher['amount'], $this->session->data['currency']);
                $voucher['amount'] = $this->removeTrail($voucher['amount']);

                $this->data['vouchers'][] = array(
                    'key' => $key,
                    'description' => $this->language->get('item_voucher'),
                    'amount' => $voucher['amount'],
                );
            }
        }

        $total_data	= array();
        $taxes		= $this->cart->getTaxes();
        $total		= 0;

        $results = $this->model_setting_extension->getExtensions('total');

        $sort_order	= array();

        foreach ($results as $key => $value) { $sort_order[$key] = $this->config->get($value['code'] . '_sort_order'); }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get($result['code'] . '_status')) {
                $this->load->model('total/' . $result['code']);
                $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
            }
        }

        $sort_order = array();

        foreach ($total_data as $key => $value) { $sort_order[$key] = $value['sort_order']; }

        array_multisort($sort_order, SORT_ASC, $total_data);

        $this->data['totals'] = array();

        foreach ($total_data as $total) {
            $total['value'] = $this->removeTrail($this->currency->format($total['value'], $this->session->data['currency']));

            $this->data['totals'][] = array(
                'title' => $total['title'],
                'text'  => $total['value']
            );
        }

        $this->template = 'default/template/svea/cart.tpl';

        $this->response->setOutput($this->render());
    }

    private function removeTrail($price)
    {
        return str_replace($this->language->get('decimal_point') . '00', '', $price);
    }

}
