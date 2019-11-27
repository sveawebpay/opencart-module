<?php

require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class SveaCommon extends Controller
{

    private $totalString = "total_";

    public function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->totalString = "";
        }
    }

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
        $vouchers = isset($this->session->data['vouchers']) ? $this->session->data['vouchers'] : null;

        if (!empty($vouchers)) {
            foreach ($vouchers as $voucher) {
                if (mb_strlen($voucher['description']) > 40) {
                    $voucher['description'] = mb_substr($voucher['description'], 0, 37) . "...";
                }
                $svea = $svea
                    ->addOrderRow(\Svea\WebPay\WebPayItem::orderRow()
                        ->setQuantity(1)
                        ->setAmountIncVat(floatval($voucher['amount']))
                        ->setVatPercent(0)//no vat when buying a voucher
                        ->setDescription($voucher['description'])
                        ->setName($voucher['description'])
                    );
            }
        }

        foreach ($addons as $addon) {
            if (isset($addon['title']) && mb_strlen($addon['title']) > 40) {
                $addon['title'] = mb_substr($addon['title'], 0, 37) . "...";
            }

            if ($addon['value'] >= 0) {
                $vat = floatval($addon['value'] * $currencyValue) * (round($addon['tax_rate']) / 100);
                $svea = $svea
                    ->addOrderRow(\Svea\WebPay\WebPayItem::orderRow()
                        ->setQuantity(1)
                        ->setAmountIncVat(floatval($addon['value'] * $currencyValue) + $vat)
                        ->setVatPercent(round($addon['tax_rate']))
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
            else
            {
                $vat = round(($addon['value'] * $currencyValue) * ($addon['tax_rate'] / 100), 2, PHP_ROUND_HALF_DOWN);

                $discountRows = $this->splitDiscount($svea->orderRows, ((abs($addon['value']) * $currencyValue) + abs($vat)), $addon['title'] , array_key_exists('text', $addon) ? $addon['text'] : "");
                foreach ($discountRows as $row) {
                    $svea = $svea->addDiscount($row);
                }
            }
        }


        return $svea;
    }

    private function splitDiscount($orderRows, $discountAmount, $discountName, $discountDescription)
    {
        $orderTotal = null;

        $splitPercent = array();

        foreach($orderRows as $row)
        {
            $orderTotal = $orderTotal + $row->amountIncVat;
        }

        foreach($orderRows as $row)
        {
            $vatPercentFound = false;
            foreach($splitPercent as $key => $val)
            {
                if((isset($row->amountIncVat) && $row->amountIncVat != 0) || (isset($row->amountExVat) && $row->amountExVat !=0))
                {
                    if(isset($splitPercent[$key]['vatPercent']) && $splitPercent[$key]['vatPercent']  == $row->vatPercent)
                    {
                        $vatPercentFound = true;
                        $splitPercent[$key]['amountIncVat'] = $splitPercent[$key]['amountIncVat'] + $row->amountIncVat / $orderTotal;
                    }
                }
            }
            if((isset($row->amountIncVat) && $row->amountIncVat != 0) || (isset($row->amountExVat) && $row->amountExVat !=0)) {
                if ($vatPercentFound == false) {
                    array_push($splitPercent,
                        array(
                            "amountIncVat" => $row->amountIncVat / $orderTotal, //
                            "vatPercent" => $row->vatPercent));
                }
            }
        }

        $discountRows = array();

        foreach($splitPercent as $val)
        {
            if($val['amountIncVat'] > 0) {
                $rowName = mb_substr($discountName . " " . $this->language->get('text_tax_class') . ":" . $val['vatPercent'] . '%', 0, 40);
                array_push($discountRows, \Svea\Webpay\WebpayItem::fixedDiscount()
                    ->setAmountIncVat($discountAmount * $val['amountIncVat'])
                    ->setVatPercent($val['vatPercent'])
                    ->setName(isset($rowName) ? $rowName : "")
                    ->setDescription((isset($discountDescription) ? $discountDescription : ""))
                );
            }
        }

        return $discountRows;
    }

    function addTaxRateToAddons()
    {
        SveaCommon::setVersionStrings();
        //Get all addons
        if(VERSION < 3.0)
        {
            $this->load->model('extension/extension');
        }
        else
        {
            $this->load->model('setting/extension');
        }

        $total_data = array();

        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        $total_data['totals'] = &$totals;
        $total_data['taxes'] = &$taxes;
        $total_data['total'] = &$total;

        $svea_tax = array();
        $cartTax = $this->cart->getTaxes();

        $sort_order = array();

        if(VERSION < 3.0)
        {
            $extensions = $this->model_extension_extension->getExtensions('total');
        }
        else
        {
            $extensions = $this->model_setting_extension->getExtensions('total');
        }


        foreach ($extensions as $key => $value) {
            $sort_order[$key] = $this->config->get($this->totalString . '' . $value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $extensions);


        $prev = null;

        foreach ($extensions as $extension) {

            //if this result is activated
            if ($this->config->get($this->totalString . '' . $extension['code'] . '_status')) {
                $amount = 0;

                $this->load->model('extension/total/' . $extension['code']);

                $this->{'model_extension_total_' . $extension['code']}->getTotal($total_data);

                foreach ($taxes as $tax_id => $value) {
                    $amount += $value;
                }

                if($prev == null)
                {
                    $prev = $amount;
                }

                $svea_tax[$extension['code']] = $amount - $prev;
                $prev = $amount;
            }
        }

        foreach ($total_data['totals'] as $key => $value) {
            if (isset($svea_tax[$value['code']])) {
                if ($svea_tax[$value['code']]) {
                    // round and cast, or may get i.e. 24.9999, which shows up as 25f in debugger & written to screen, but converts to 24i
                    $total_data['totals'][$key]['tax_rate'] = ($svea_tax[$value['code']] / $value['value']) * 100;
                } else {
                    $total_data['totals'][$key]['tax_rate'] = 0;
                }
            } else {
                $total_data['totals'][$key]['tax_rate'] = '0';
            }
        }

        // for any order totals that are sorted below taxes, set the extension tax rate to zero
        $tax_sort_order = $this->config->get($this->totalString . 'tax_sort_order');
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
