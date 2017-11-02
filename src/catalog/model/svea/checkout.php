<?php

class ModelSveaCheckout extends Model
{

    public function getCheckoutProducts($order_id)
    {
        $order_id = (int)$order_id;

        $query = $this->db->query("SELECT product_id, name, model, (price + tax) as price, quanity
								   FROM `" . DB_PREFIX . "`order_product 
								   WHERE order_id = '" . $order_id . "'");

        return isset($query->row['product_id']) ? $query->rows : null;
    }


    public function addOrder($order)
    {
        $order_id = (int)$order['order_id'];

        if ($order_id) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "order_option`  WHERE order_id = '" . (int)$order_id . "'");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_id . "'");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "order_total`   WHERE order_id = '" . (int)$order_id . "'");
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "order` 
							SET
								order_id 					= '" . (int)$order['order_id'] . "',
								invoice_prefix 				= '" . $this->db->escape($order['invoice_prefix']) . "',
								store_id 					= '" . (int)$order['store_id'] . "',
								store_name 					= '" . $this->db->escape($order['store_name']) . "',
								store_url 					= '" . $this->db->escape($order['store_url']) . "',
								customer_id 				= '" . (int)$order['customer_id'] . "',
								customer_group_id 			= '" . (int)$order['customer_group_id'] . "',
								firstname 					= '" . $this->db->escape($order['firstname']) . "',
								lastname 					= '" . $this->db->escape($order['lastname']) . "',
								email 						= '" . $this->db->escape($order['email']) . "',
								telephone 					= '" . $this->db->escape($order['telephone']) . "',
								payment_firstname 			= '" . $this->db->escape($order['payment_firstname']) . "',
								payment_lastname 			= '" . $this->db->escape($order['payment_lastname']) . "',
								payment_company 			= '" . $this->db->escape($order['payment_company']) . "',
								payment_address_1 			= '" . $this->db->escape($order['payment_address_1']) . "',
								payment_address_2 			= '" . $this->db->escape($order['payment_address_2']) . "',
								payment_city 				= '" . $this->db->escape($order['payment_city']) . "',
								payment_postcode 			= '" . $this->db->escape($order['payment_postcode']) . "',
								payment_country 			= '" . $this->db->escape($order['payment_country']) . "',
								payment_country_id 			= '" . (int)$order['payment_country_id'] . "',
								payment_zone 				= '" . $this->db->escape($order['payment_zone']) . "',
								payment_zone_id 			= '" . (int)$order['payment_zone_id'] . "',
								payment_address_format 		= '" . $this->db->escape($order['payment_address_format']) . "',
								payment_method 				= '" . $this->db->escape($order['payment_method']) . "',
								payment_code 				= '" . $this->db->escape($order['payment_code']) . "',
								shipping_firstname 			= '" . $this->db->escape($order['shipping_firstname']) . "',
								shipping_lastname 			= '" . $this->db->escape($order['shipping_lastname']) . "',
								shipping_company 			= '" . $this->db->escape($order['shipping_company']) . "',
								shipping_address_1 			= '" . $this->db->escape($order['shipping_address_1']) . "',
								shipping_address_2 			= '" . $this->db->escape($order['shipping_address_2']) . "',
								shipping_city 				= '" . $this->db->escape($order['shipping_city']) . "',
								shipping_postcode 			= '" . $this->db->escape($order['shipping_postcode']) . "',
								shipping_country 			= '" . $this->db->escape($order['shipping_country']) . "',
								shipping_country_id 		= '" . (int)$order['shipping_country_id'] . "',
								shipping_zone 				= '" . $this->db->escape($order['shipping_zone']) . "',
								shipping_zone_id 			= '" . (int)$order['shipping_zone_id'] . "',
								shipping_address_format 	= '" . $this->db->escape($order['shipping_address_format']) . "',
								shipping_method 			= '" . $this->db->escape($order['shipping_method']) . "',
								shipping_code 				= '" . $this->db->escape($order['shipping_code']) . "',
								comment 					= '" . $this->db->escape($order['comment']) . "',
								total 						= '" . (float)$order['total'] . "',
								affiliate_id 				= '" . (int)$order['affiliate_id'] . "',
								commission 					= '" . (float)$order['commission'] . "',
								marketing_id 				= '" . (int)$order['marketing_id'] . "',
								tracking 					= '" . $this->db->escape($order['tracking']) . "',
								language_id 				= '" . (int)$order['language_id'] . "',
								currency_id 				= '" . (int)$order['currency_id'] . "',
								currency_code 				= '" . $this->db->escape($order['currency_code']) . "',
								currency_value 				= '" . (float)$order['currency_value'] . "',
								ip 							= '" . $this->db->escape($order['ip']) . "',
								forwarded_ip 				= '" . $this->db->escape($order['forwarded_ip']) . "',
								user_agent 					= '" . $this->db->escape($order['user_agent']) . "',
								accept_language 			= '" . $this->db->escape($order['accept_language']) . "',
								date_added					= NOW(),
								date_modified 				= NOW()

							ON DUPLICATE KEY UPDATE
					            
								invoice_prefix 				= '" . $this->db->escape($order['invoice_prefix']) . "',
								store_id 					= '" . (int)$order['store_id'] . "',
								store_name 					= '" . $this->db->escape($order['store_name']) . "',
								store_url 					= '" . $this->db->escape($order['store_url']) . "',
								customer_id 				= '" . (int)$order['customer_id'] . "',
								customer_group_id 			= '" . (int)$order['customer_group_id'] . "',
								firstname 					= '" . $this->db->escape($order['firstname']) . "',
								lastname 					= '" . $this->db->escape($order['lastname']) . "',
								email 						= '" . $this->db->escape($order['email']) . "',
								telephone 					= '" . $this->db->escape($order['telephone']) . "',
								payment_firstname 			= '" . $this->db->escape($order['payment_firstname']) . "',
								payment_lastname 			= '" . $this->db->escape($order['payment_lastname']) . "',
								payment_company 			= '" . $this->db->escape($order['payment_company']) . "',
								payment_address_1 			= '" . $this->db->escape($order['payment_address_1']) . "',
								payment_address_2 			= '" . $this->db->escape($order['payment_address_2']) . "',
								payment_city 				= '" . $this->db->escape($order['payment_city']) . "',
								payment_postcode 			= '" . $this->db->escape($order['payment_postcode']) . "',
								payment_country 			= '" . $this->db->escape($order['payment_country']) . "',
								payment_country_id 			= '" . (int)$order['payment_country_id'] . "',
								payment_zone 				= '" . $this->db->escape($order['payment_zone']) . "',
								payment_zone_id 			= '" . (int)$order['payment_zone_id'] . "',
								payment_address_format 		= '" . $this->db->escape($order['payment_address_format']) . "',
								payment_method 				= '" . $this->db->escape($order['payment_method']) . "',
								payment_code 				= '" . $this->db->escape($order['payment_code']) . "',
								shipping_firstname 			= '" . $this->db->escape($order['shipping_firstname']) . "',
								shipping_lastname 			= '" . $this->db->escape($order['shipping_lastname']) . "',
								shipping_company 			= '" . $this->db->escape($order['shipping_company']) . "',
								shipping_address_1 			= '" . $this->db->escape($order['shipping_address_1']) . "',
								shipping_address_2 			= '" . $this->db->escape($order['shipping_address_2']) . "',
								shipping_city 				= '" . $this->db->escape($order['shipping_city']) . "',
								shipping_postcode 			= '" . $this->db->escape($order['shipping_postcode']) . "',
								shipping_country 			= '" . $this->db->escape($order['shipping_country']) . "',
								shipping_country_id 		= '" . (int)$order['shipping_country_id'] . "',
								shipping_zone 				= '" . $this->db->escape($order['shipping_zone']) . "',
								shipping_zone_id 			= '" . (int)$order['shipping_zone_id'] . "',
								shipping_address_format 	= '" . $this->db->escape($order['shipping_address_format']) . "',
								shipping_method 			= '" . $this->db->escape($order['shipping_method']) . "',
								shipping_code 				= '" . $this->db->escape($order['shipping_code']) . "',
								comment 					= '" . $this->db->escape($order['comment']) . "',
								total 						= '" . (float)$order['total'] . "',
								commission 					= '" . (float)$order['commission'] . "',
								tracking 					= '" . $this->db->escape($order['tracking']) . "',
								language_id 				= '" . (int)$order['language_id'] . "',
								currency_id 				= '" . (int)$order['currency_id'] . "',
								currency_code 				= '" . $this->db->escape($order['currency_code']) . "',
								currency_value 				= '" . (float)$order['currency_value'] . "',
								date_modified 				= NOW()
		");

        $order_id = (!$order_id) ? $this->db->getLastId() : $order_id;

        if (isset($order['products'])) {

            foreach ($order['products'] as $product) {

                $this->db->query("
					INSERT INTO `" . DB_PREFIX . "order_product` SET
					`order_id`	    = '" . (int)$order_id . "',
					`product_id`	= '" . (int)$product['product_id'] . "',
					`name`		    = '" . $this->db->escape($product['name']) . "',
					`model`		    = '" . $this->db->escape($product['model']) . "',
					`quantity`	    = '" . (int)$product['quantity'] . "',
					`price`		    = '" . (float)$product['price'] . "',
					`total`		    = '" . (float)$product['total'] . "',
					`tax`			= '" . (float)$product['tax'] . "',
					`reward`		= '" . (int)$product['reward'] . "'
				");

                $order_product_id = $this->db->getLastId();

                foreach ($product['option'] as $option) {
                    $this->db->query("
						INSERT INTO `" . DB_PREFIX . "order_option` 
						SET
                            `order_id`				    = '" . (int)$order_id . "',
                            `order_product_id`		    = '" . (int)$order_product_id . "',
                            `product_option_id`		    = '" . (int)$option['product_option_id'] . "',
                            `product_option_value_id`	= '" . (int)$option['product_option_value_id'] . "',
                            `name`					    = '" . $this->db->escape($option['name']) . "',
                            `value`					    = '" . $this->db->escape($option['value']) . "',
                            `type`					    = '" . $this->db->escape($option['type']) . "'
					");
                }

            }

        }

        $this->load->model('total/voucher');

        $this->model_total_voucher->disableVoucher($order_id);

        if (isset($order['vouchers'])) {

            foreach ($order['vouchers'] as $voucher) {

                $this->db->query("
					INSERT INTO `" . DB_PREFIX . "order_voucher` 
					SET
                        order_id			= '" . (int)$order_id . "',
                        description			= '" . $this->db->escape($voucher['description']) . "',
                        code				= '" . $this->db->escape($voucher['code']) . "',
                        from_name			= '" . $this->db->escape($voucher['from_name']) . "',
                        from_email			= '" . $this->db->escape($voucher['from_email']) . "',
                        to_name				= '" . $this->db->escape($voucher['to_name']) . "',
                        to_email			= '" . $this->db->escape($voucher['to_email']) . "',
                        voucher_theme_id	= '" . (int)$voucher['voucher_theme_id'] . "',
                        message				= '" . $this->db->escape($voucher['message']) . "',
                        amount				= '" . (float)$voucher['amount'] . "'
				");

                $order_voucher_id = $this->db->getLastId();

                $voucher_id = $this->model_total_voucher->addVoucher($order_id, $voucher);

                $this->db->query("UPDATE `" . DB_PREFIX . "order_voucher` 
								  SET voucher_id = '" . (int)$voucher_id . "' 
								  WHERE order_voucher_id = '" . (int)$order_voucher_id . "'");
            }
        }

        if (isset($order['totals'])) {

            foreach ($order['totals'] as $total) {

                $this->db->query("INSERT INTO `" . DB_PREFIX . "order_total` 
								  SET
										order_id	= '" . (int)$order_id . "',
										code		= '" . $this->db->escape($total['code']) . "',
										title		= '" . $this->db->escape($total['title']) . "',
										value		= '" . (float)$total['value'] . "',
										sort_order	= '" . (int)$total['sort_order'] . "'");
            }
        }

        return $order_id;

    }

    public function updateOrder($order_id, $response)
    {
        $order_id = (int)$order_id;

        $data = array();

        $is_company = $response['customer']['iscompany'];

        if (isset($response['phonenumber'])) {
            $data['telephone'] = $this->db->escape($response['phonenumber']);
        }
        if (isset($response['emailaddress']))
        {
            $data['email'] = $this->db->escape($response['emailaddress']);
        }
        // Payment data
        if (isset($response['billingaddress']) && is_array($response['billingaddress'])) {
            $billing_address = $response['billingaddress'];

            if (isset($billing_address['firstname']) && $billing_address['firstname'] != "") {
                $data['payment_firstname'] = $this->db->escape($billing_address['firstname']);   
            }

            if (isset($billing_address['lastname']) && $billing_address['lastname'] != "") {
                $data['payment_lastname'] = $this->db->escape($billing_address['lastname']);
            }

            if ($is_company === true && empty($billing_address['firstname']) && empty($billing_address['lastname'])) {
                $data['payment_firstname'] = $this->db->escape($billing_address['fullname']);
            }

            if (isset($billing_address['streetaddress']) && $billing_address['streetaddress'] != "") {
                $data['payment_address_1'] = $this->db->escape($billing_address['streetaddress']);
            }

            if (isset($billing_address['coaddress']) && $billing_address['coaddress'] != "") {
                $data['payment_address_2'] = $this->db->escape($billing_address['coaddress']);
            }

            if (isset($billing_address['city']) && $billing_address['city'] != "") {
                $data['payment_city'] = $this->db->escape($billing_address['city']);
            }

            if (isset($billing_address['postalcode']) && $billing_address['postalcode'] != "") {
                $data['payment_postcode'] = $this->db->escape($billing_address['postalcode']);
            }

            $billing_country = $this->getCountry($billing_address['countrycode']);

            if ($billing_country) {
                $data['payment_country'] = $this->db->escape($billing_country['name']);
                $data['payment_country_id'] = (int)$billing_country['country_id'];
                $data['payment_address_format'] = $this->db->escape($billing_country['address_format']);
            }

            // Set customer information
            if (isset($billing_address['firstname']) && $billing_address['firstname'] != "") {
                $data['firstname'] = $this->db->escape($billing_address['firstname']);
            }

            if (isset($billing_address['lastname']) && $billing_address['lastname'] != "") {
                $data['lastname'] = $this->db->escape($billing_address['lastname']);
            }

            if ($is_company === true && empty($billing_address['firstname']) && empty($billing_address['lastname'])) {
                $data['firstname'] = $this->db->escape($billing_address['fullname']);
            }
        }

        // Shipping data
        if (isset($response['shippingaddress']) && is_array($response['shippingaddress'])) {
            $shipping_address = $response['shippingaddress'];

            if (isset($shipping_address['firstname']) && $shipping_address['firstname'] != "") {
                $data['shipping_firstname'] = $this->db->escape($shipping_address['firstname']);
            }

            if (isset($shipping_address['lastname']) && $shipping_address['lastname'] != "") {
                $data['shipping_lastname'] = $this->db->escape($shipping_address['lastname']);
            }

            if ($is_company === true && empty($shipping_address['firstname']) && empty($shipping_address['lastname'])) {
                $data['shipping_firstname'] = $this->db->escape($shipping_address['fullname']);
            }

            if (isset($shipping_address['streetaddress']) && $shipping_address['streetaddress'] != "") {
                $data['shipping_address_1'] = $this->db->escape($shipping_address['streetaddress']);
            }

            if (isset($shipping_address['coaddress']) && $shipping_address['coaddress'] != "") {
                $data['shipping_address_2'] = $this->db->escape($shipping_address['coaddress']);
            }

            if (isset($shipping_address['city']) && $shipping_address['city'] != "") {
                $data['shipping_city'] = $this->db->escape($shipping_address['city']);
            }

            if (isset($shipping_address['postalcode']) && $shipping_address['postalcode'] != "") {
                $data['shipping_postcode'] = $this->db->escape($shipping_address['postalcode']);
            }

            $shipping_country = $this->getCountry($shipping_address['countrycode']);

            if ($shipping_country) {
                $data['shipping_country'] = $this->db->escape($shipping_country['name']);
                $data['shipping_country_id'] = (int)$shipping_country['country_id'];
                $data['shipping_address_format'] = $this->db->escape($shipping_country['address_format']);
            }
        }

        $query = "UPDATE `" . DB_PREFIX . "order` 
				    SET ";

        foreach ($data as $key => $val) {
            $query .= "$key = '" . $val . "', ";
        }

        $query .= "     date_modified = NOW()
				   WHERE order_id = '" . (int)$order_id . "'
				   LIMIT 1 ";

        $this->db->query($query);

        $oc_order_status_id = $this->getOrderStatusIdFromResponse($response);
        $this->load->model('checkout/order');
        $sco_order_id = $response['orderid'];
        $comment =  'Svea Checkout Order Id: '. $sco_order_id;

        // CONFIRM ORDER
        $this->model_checkout_order->addOrderHistory($order_id, $oc_order_status_id, $comment, true);

        // Set order status
        if ($oc_order_status_id !== null) {
            $query = "UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $oc_order_status_id . "',";
            $query .= " date_modified = NOW() WHERE order_id = '" . (int)$order_id . "' LIMIT 1 ";
            $this->db->query($query);
        }

        return true;
    }

    private function getOrderStatusIdFromResponse($response)
    {
        $oc_order_status_id = null;

        if (isset($response['status'])) {
            $sco_order_status_string = strtolower($response['status']);
            if ($sco_order_status_string === 'paymentguaranteed' || $sco_order_status_string === 'final') {
                $oc_order_status_id = $this->config->get('config_order_status_id');
            } else if ($sco_order_status_string === 'cancelled') {
                $oc_order_status_id = $this->config->get('sco_failed_status_id');
            }
        }

        return $oc_order_status_id;
    }

    public function getCountry($country_code)
    {
        $country_code = $this->db->escape($country_code);

        $query = $this->db->query("SELECT * 
                                   FROM `" . DB_PREFIX . "country`
                                   WHERE iso_code_2 = '" . $country_code . "'
                                      OR iso_code_3 = '" . $country_code . "'
                                   LIMIT 1");

        return (isset($query->row['country_id'])) ? $query->row : NULL;
    }


    public function addCheckoutOrder($order_id, $locale)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "order_sco` 
						  SET
								order_id 		= '" . (int)$order_id . "',
								locale 			= '" . $this->db->escape($locale) . "',
								date_added		= NOW(),
								date_modified 	= NOW()
			
						  ON DUPLICATE KEY UPDATE
						  
								locale 			= '" . $this->db->escape($locale) . "',
								date_modified 	= NOW()");
    }


    public function updateCheckoutOrder($data)
    {
        $order_id = (int)$data['order_id'];

        $query = "UPDATE `" . DB_PREFIX . "order_sco` 
				   SET ";

        foreach ($data as $key => $val) {
            if ($key != 'date_created') {
                $val = $this->db->escape($val);
            }
            $query .= "$key = '" . $val . "',";
        }

        $query .= "     date_modified 	= NOW()
				   WHERE order_id = '" . $order_id . "'
				   LIMIT 1";

        $this->db->query($query);

        /*$this->db->query("UPDATE `" . DB_PREFIX . "order_sco`
						  SET
								checkout_id 	= '" . $this->db->escape($data['checkout_id']) . "',
								reservation 	= '" . $this->db->escape($data['reservation']) . "',
								reference 		= '" . $this->db->escape($data['reference']) . "',
								status 			= 'created',
								type 			= '" . $this->db->escape($data['type']) . "',
								date_of_birth 	= '" . $this->db->escape($data['date_of_birth']) . "',
								gender 			= '" . $this->db->escape($data['gender']) . "',
								country			= '" . $this->db->escape($data['country']) . "',
								currency		= '" . $this->db->escape($data['currency']) . "',
								locale			= '" . $this->db->escape($data['locale']) . "',
								date_created 	= NOW(),
								date_modified 	= NOW()
			  			  WHERE order_id = '" . (int)$data['order_id'] . "'
						  LIMIT 1");*/

        return true;
    }


    public function getCheckoutOrder($order_id)
    {
        $order_id = (int)$order_id;

        $query = $this->db->query("SELECT * 
								   FROM `" . DB_PREFIX . "order_sco` 
								   WHERE order_id = '" . $order_id . "' 
								   LIMIT 1");

        return (isset($query->row['order_id'])) ? $query->row : NULL;
    }

    public function addComment($order_id, $comment)
    {
        $order_id = (int)$order_id;
        $comment = $this->db->escape($comment);

        $this->db->query("UPDATE `" . DB_PREFIX . "order` 
						  SET 
						  		comment = '" . $comment . "' 
						  WHERE order_id = '" . $order_id . "' 
						  LIMIT 1");
    }


    public function getCustomersByEmail($email)
    {
        $email = $this->db->escape(utf8_strtolower($email));

        $query = $this->db->query("SELECT COUNT(*) AS total 
								   FROM `" . DB_PREFIX . "customer` 
								   WHERE LOWER(email) = '" . $email . "'");

        return (isset($query->row['total'])) ? $query->row['total'] : NULL;
    }

    public function getCustomerFromOrder($order_id)
    {
        $order_id = (int)$order_id;

        $result = $this->db->query("SELECT t1.customer_id, t2.address_id 
									FROM `" . DB_PREFIX . "order` AS t1
									LEFT JOIN `" . DB_PREFIX . "customer` AS t2 ON t1.customer_id = t2.customer_id
									WHERE order_id = '" . $order_id . "'
									LIMIT 1");

        return (isset($result->row['customer_id'])) ? $result->row : false;
    }

    public function getPostcode($address_id)
    {
        $address_id = (int)$address_id;

        $query = $this->db->query("SELECT postcode 
								   FROM `" . DB_PREFIX . "address` 
								   WHERE address_id = '" . $address_id . "' 
								   LIMIT 1");

        return (isset($query->row['postcode'])) ? $query->row['postcode'] : NULL;
    }


    public function updateCustomer($data)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "customer` 
						  SET
								firstname	= '" . $this->db->escape($data['firstname']) . "',
								lastname	= '" . $this->db->escape($data['lastname']) . "',
								telephone	= '" . $this->db->escape($data['telephone']) . "'
						  WHERE customer_id = '" . (int)$data['customer_id'] . "' 
						  LIMIT 1");

        if ($data['address_id']) {

            $this->db->query("UPDATE `" . DB_PREFIX . "address` 
							  SET
									firstname	= '" . $this->db->escape($data['firstname']) . "',
									lastname	= '" . $this->db->escape($data['lastname']) . "',
									address_1	= '" . $this->db->escape($data['address_1']) . "',
									address_2	= '" . $this->db->escape($data['address_2']) . "',
									city		= '" . $this->db->escape($data['city']) . "',
									postcode	= '" . $this->db->escape($data['postcode']) . "',
									country_id	= '" . (int)$data['country_id'] . "',
									zone_id		= '0'
									WHERE customer_id = '" . (int)$data['customer_id'] . "' 
									  AND address_id = '" . (int)$data['address_id'] . "' 
									LIMIT 1");
        }

        return true;
    }

    public function getGAOrder($order_id)
    {
        $order_id = (int)$order_id;

        $query = $this->db->query("
			SELECT
				(SELECT store_name FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $order_id . "') AS store_name,
				(SELECT SUM(`value`) FROM `" . DB_PREFIX . "order_total` WHERE `code` = 'total' AND order_id = '" . $order_id . "') AS revenue,
				(SELECT SUM(`value`) FROM `" . DB_PREFIX . "order_total` WHERE `code` = 'shipping' AND order_id = '" . $order_id . "') AS shipping,
				(SELECT SUM(`value`) FROM `" . DB_PREFIX . "order_total` WHERE `code` = 'tax' AND order_id = '" . $order_id . "') AS tax
			LIMIT 1");

        return (isset($query->row['revenue'])) ? $query->row : NULL;
    }

    public function getGAProducts($order_id)
    {
        $order_id = (int)$order_id;

        $query = $this->db->query("SELECT product_id, name, model AS sku, (price + tax) AS price, quantity 
								   FROM `" . DB_PREFIX . "order_product` 
								   WHERE order_id = '" . $order_id . "'");

        return (isset($query->row['product_id'])) ? $query->rows : NULL;
    }
}
