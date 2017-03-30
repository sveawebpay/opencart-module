<?php

require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class SveaCommon extends Controller
{

    function addOrderRowsToHostedServiceOrder($svea, $products, $currencyValue)
    {

        foreach ($products as $product) {
            $product['name'] = $this->db->escape($product['name']);
            if (mb_strlen($product['name']) > 40) {
                $product['name'] = mb_substr($product['name'], 0, 37) . "...";
            }

            $item = \Svea\WebPay\WebPayItem::orderRow()
                ->setQuantity(intval($product['quantity']))
                ->setName($product['name'])
                ->setArticleNumber($product['model'])//->setDescription($product['model'])//should be used for $product['option'] which is array, but too risky since limit is String(40)
            ;

            $productPriceExVat = $product['price'] * $currencyValue;
            $taxPercent = 0;
            //Get the tax, difference in version 1.4.x
            $tax = $this->tax->getRates($product['price'], $product['tax_class_id']);
            foreach ($tax as $key => $value) {
                $taxPercent = $value['rate'];
            }

            $item = $item
                ->setAmountExVat(floatval($productPriceExVat))
                ->setVatPercent(intval($taxPercent));

            $svea = $svea->addOrderRow($item);
        }

        return $svea;
    }

    function addOrderRowsToWebServiceOrder($svea, $products, $currencyValue)
    {

        foreach ($products as $product) {
            $product['name'] = $this->db->escape($product['name']);
            if (mb_strlen($product['name']) > 40) {
                $product['name'] = mb_substr($product['name'], 0, 37) . "...";
            }

            $item = \Svea\WebPay\WebPayItem::orderRow()
                ->setQuantity(intval($product['quantity']))
                ->setName($product['name'])
                ->setArticleNumber($product['model'])//->setDescription($product['model'])//should be used for $product['option'] which is array, but too risky since limit is String(40)
            ;

            $tax = $this->tax->getRates($product['price'], $product['tax_class_id']);
            $taxPercent = 0;
            $taxAmount = 0;
            foreach ($tax as $key => $value) {
                $taxPercent = $value['rate'];
                $taxAmount = $value['amount'];
            }

            $item = $item
                ->setAmountIncVat(($product['price'] + $taxAmount) * $currencyValue)
                ->setVatPercent(intval($taxPercent));

            $svea = $svea->addOrderRow($item);
        }

        return $svea;
    }

    function addAddonRowsToSveaOrder($svea, $addons, $currencyValue)
    {
        //purchased vouchers
        $vouchers = $this->db->query(
            "SELECT `code`, `description`, `amount`
        FROM `" . DB_PREFIX . "order_voucher`
        WHERE `order_id` = " . (int)$this->session->data['order_id']);
        if ($vouchers->num_rows >= 1) {
            foreach ($vouchers->rows as $voucher) {
                $svea = $svea
                    ->addOrderRow(\Svea\WebPay\WebPayItem::orderRow()
                        ->setQuantity(1)
                        ->setAmountIncVat(floatval($voucher['amount']))
                        ->setVatPercent(0)//no vat when buying a voucher
                        ->setArticleNumber($voucher['code'])
                        ->setDescription($voucher['description'])
                    );
            }
        }
        foreach ($addons as $addon) {
            if (isset($addon['title']) && mb_strlen($addon['title']) > 40) {
                $addon['title'] = mb_substr($addon['title'], 0, 37) . "...";
            }

            if ($addon['value'] >= 0) {
                $vat = floatval($addon['value'] * $currencyValue) * (intval($addon['tax_rate']) / 100);
                $svea = $svea
                    ->addOrderRow(\Svea\WebPay\WebPayItem::orderRow()
                        ->setQuantity(1)
                        ->setAmountIncVat(floatval($addon['value'] * $currencyValue) + $vat)
                        ->setVatPercent(intval($addon['tax_rate']))
                        ->setName(isset($addon['title']) ? $addon['title'] : "")
                        ->setArticleNumber($addon['code'])
                        ->setDescription(isset($addon['text']) ? $addon['text'] : "")
                    );
            } //used voucher(-)
            elseif ($addon['value'] < 0 && $addon['code'] == 'voucher') {
                $svea = $svea
                    ->addDiscount(\Svea\WebPay\WebPayItem::fixedDiscount()
                        ->setDiscountId($addon['code'])
                        ->setAmountIncVat(floatval(abs($addon['value']) * $currencyValue))
                        ->setVatPercent(0)//no vat when using a voucher
                        ->setName(isset($addon['title']) ? $addon['title'] : "")
                        ->setDescription(isset($addon['text']) ? $addon['text'] : "")
                    );
            } //discounts (-)
            else {
                $vat = floatval($addon['value'] * $currencyValue) * (intval($addon['tax_rate']) / 100);

                $discountRows = \Svea\WebPay\Helper\Helper::splitMeanAcrossTaxRates(
                    ((abs($addon['value']) * $currencyValue) + abs($vat)),
                    $addon['tax_rate'],
                    $addon['title'],
                    array_key_exists('text', $addon) ? $addon['text'] : "",
                    \Svea\WebPay\Helper\Helper::getTaxRatesInOrder($svea),
                    false) // discount rows will use amountIncVat
                ;
                foreach ($discountRows as $row) {
                    $svea = $svea->addDiscount($row);
                }
            }
        }


        return $svea;
    }

    function addTaxRateToAddons()
    {
        //Get all addons
        $this->load->model('extension/extension');
        $total_data = array();

        $totals = array();
        $taxes = array();
        $total = 0;

        $total_data['totals'] = &$totals;
        $total_data['taxes'] = &$taxes;
        $total_data['total'] = &$total;

        $svea_tax = array();
        $cartTax = $this->cart->getTaxes();

        $sort_order = array();

        $extensions = $this->model_extension_extension->getExtensions('total');

        foreach ($extensions as $key => $value) {
            $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $extensions);


        foreach ($extensions as $extension) {

            //if this result is activated
            if ($this->config->get($extension['code'] . '_status')) {
                $amount = 0;

                foreach ($cartTax as $key => $value) {
                    $taxes[$key] = 0;
                }
                $this->load->model('extension/total/' . $extension['code']);

                $this->{'model_extension_total_' . $extension['code']}->getTotal($total_data);

                foreach ($taxes as $tax_id => $value) {
                    $amount += $value;
                }

                $svea_tax[$extension['code']] = $amount;
            }
        }

        foreach ($total_data['totals'] as $key => $value) {
            if (isset($svea_tax[$value['code']])) {
                if ($svea_tax[$value['code']]) {
                    // round and cast, or may get i.e. 24.9999, which shows up as 25f in debugger & written to screen, but converts to 24i
                    $total_data['totals'][$key]['tax_rate'] = (int)round($svea_tax[$value['code']] / $value['value'] * 100);
                } else {
                    $total_data['totals'][$key]['tax_rate'] = 0;
                }
            } else {
                $total_data['totals'][$key]['tax_rate'] = '0';
            }
        }

        // for any order totals that are sorted below taxes, set the extension tax rate to zero
        $tax_sort_order = $this->config->get('tax_sort_order');
        foreach ($total_data['totals'] as $key => $value) {
            if ($total_data['totals'][$key]['sort_order'] > $tax_sort_order) {
                $total_data['totals'][$key]['tax_rate'] = 0;
            }
        }

        // remove order totals that won't be added as rows to createOrder
        $ignoredTotals = 'sub_total, total, taxes, tax';
        $ignoredOrderTotals = array_map('trim', explode(',', $ignoredTotals));
        foreach ($total_data['totals'] as $key => $orderTotal) {
            if (in_array($orderTotal['code'], $ignoredOrderTotals)) {
                unset($total_data['totals'][$key]);
            }
        }

        return $total_data['totals'];
    }

}
