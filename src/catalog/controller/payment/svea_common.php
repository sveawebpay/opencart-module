<?php
class SveaCommon extends Controller {

    function addOrderRowsToHostedServiceOrder($svea,$products,$currencyValue) {

        foreach($products as $product){
            $item = WebPayItem::orderRow()
                ->setQuantity($product['quantity'])
                ->setName($product['name'])
                ->setUnit($this->language->get('unit'))
                ->setArticleNumber($product['model'])
                //->setDescription($product['model'])//should be used for $product['option'] which is array, but too risky since limit is String(40)
            ;

            $productPriceExVat  = $product['price'] * $currencyValue;
            $taxPercent = 0;
            if(floatval(VERSION) >= 1.5){   //Get the tax, difference in version 1.4.x
                $tax = $this->tax->getRates($product['price'], $product['tax_class_id']);
                foreach ($tax as $key => $value) {
                    $taxPercent = $value['rate'];
                }
            } 
            else {
                $taxPercent = $this->tax->getRate($product['tax_class_id']);
            }

            $item = $item
                ->setAmountExVat(floatval($productPriceExVat))
                ->setVatPercent(intval($taxPercent))
            ;

            $svea = $svea->addOrderRow( $item );
        }

        return $svea;
    }    
    
    function addOrderRowsToWebServiceOrder($svea,$products,$currencyValue){

        foreach ($products as $product) {
            $item = WebPayItem::orderRow()
                ->setQuantity($product['quantity'])
                ->setName($product['name'])
                ->setUnit($this->language->get('unit'))
                ->setArticleNumber($product['model'])
                //->setDescription($product['model'])//should be used for $product['option'] which is array, but too risky since limit is String(40)
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
                ->setVatPercent(intval($taxPercent))
            ;
            
            $svea = $svea->addOrderRow($item);
        }
        return $svea;
    }    
    
    function addAddonRowsToSveaOrder( $svea, $addons, $currencyValue ) {
    
        foreach ($addons as $addon) {            
            if($addon['value'] >= 0) {
                $vat = floatval($addon['value'] * $currencyValue) * (intval($addon['tax_rate']) / 100 );
                $svea = $svea
                    ->addOrderRow(WebPayItem::orderRow()
                    ->setQuantity(1)
                    ->setAmountIncVat(floatval($addon['value'] * $currencyValue) + $vat)
                    ->setVatPercent(intval($addon['tax_rate']))
                    ->setName(isset($addon['title']) ? $addon['title'] : "")
                    ->setUnit($this->language->get('unit'))
                    ->setArticleNumber($addon['code'])
                    ->setDescription(isset($addon['text']) ? $addon['text'] : "")
                );
            }
            
            //voucher(-)
            elseif ($addon['value'] < 0 && $addon['code'] == 'voucher') {
                $svea = $svea
                    ->addDiscount(WebPayItem::fixedDiscount()
                        ->setDiscountId($addon['code'])
                        ->setAmountIncVat(floatval(abs($addon['value']) * $currencyValue))
                        ->setVatPercent(0)//no vat when using a voucher
                        ->setName(isset($addon['title']) ? $addon['title'] : "")
                        ->setUnit($this->language->get('unit'))
                        ->setDescription(isset($addon['text']) ? $addon['text'] : "")
                );
            }
            //discounts (-)
            else {             
                $vat = floatval($addon['value'] * $currencyValue) * (intval($addon['tax_rate']) / 100 );

                $discountRows = Svea\Helper::splitMeanAcrossTaxRates( 
                        ((abs($addon['value']) * $currencyValue) + abs($vat)),
                        $addon['tax_rate'], 
                        $addon['title'], 
                        array_key_exists('text', $addon) ? $addon['text'] : "", 
                        Svea\Helper::getTaxRatesInOrder($svea),
                        false ) // discount rows will use amountIncVat
                ;
                foreach($discountRows as $row) {
                    $svea = $svea->addDiscount( $row );
                }
            }
         }  
         
         return $svea;
    }
    
    function addTaxRateToAddons() {
        
        //Get all addons
        $this->load->model('extension/extension');
        $total_data = array();
        
        $total = 0;        
        $svea_tax = array();
        $cartTax = $this->cart->getTaxes();

        $extensions = $this->model_setting_extension->getExtensions('total'); // 1.x      
        //$extensions = $this->model_extension_extension->getExtensions('total'); // 2.x
        foreach ($extensions as $extension) {
            
            //if this result is activated
            if($this->config->get($extension['code'] . '_status')){
                $amount = 0;
                $taxes = array();

                foreach ($cartTax as $key => $value) {
                    $taxes[$key] = 0;
                }
                $this->load->model('total/' . $extension['code']);

                $this->{'model_total_' . $extension['code']}->getTotal($total_data, $total, $taxes);

                foreach ($taxes as $tax_id => $value) {
                    $amount += $value;
                }

                $svea_tax[$extension['code']] = $amount;
            }
        }        
        
        foreach ($total_data as $key => $value) {
            if (isset($svea_tax[$value['code']])) {
                if ($svea_tax[$value['code']]) {
                    $total_data[$key]['tax_rate'] = (int)round( $svea_tax[$value['code']] / $value['value'] * 100 ); // round and cast, or may get i.e. 24.9999, which shows up as 25f in debugger & written to screen, but converts to 24i
                } else {
                    $total_data[$key]['tax_rate'] = 0;
                }
            }
            else {
                $total_data[$key]['tax_rate'] = '0';
            }
        }

        // for any order totals that are sorted below taxes, set the extension tax rate to zero
        $tax_sort_order = $this->config->get('tax_sort_order');
        foreach ($total_data as $key => $value) {
            if( $total_data[$key]['sort_order'] > $tax_sort_order ) {
                $total_data[$key]['tax_rate'] = 0; 
            }
        }

        // remove order totals that won't be added as rows to createOrder
        $ignoredTotals = 'sub_total, total, taxes';
        $ignoredOrderTotals = array_map('trim', explode(',', $ignoredTotals));
        foreach ($total_data as $key => $orderTotal) {
            if (in_array($orderTotal['code'], $ignoredOrderTotals)) {
                unset($total_data[$key]);
            }
        }
        return $total_data;
    }
    
}
?>
