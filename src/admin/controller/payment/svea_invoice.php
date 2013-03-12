<?php 
class ControllerPaymentsveainvoice extends Controller {
	private $error = array(); 
	 //
	public function index() { 
		$this->load->language('payment/svea_invoice');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('svea_invoice', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->redirect(HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token']);
		}
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled']       = $this->language->get('text_enabled');
		$this->data['text_disabled']      = $this->language->get('text_disabled');
		$this->data['text_all_zones']     = $this->language->get('text_all_zones');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');		
		$this->data['entry_geo_zone']     = $this->language->get('entry_geo_zone');
		$this->data['entry_status']       = $this->language->get('entry_status');
		$this->data['entry_sort_order']   = $this->language->get('entry_sort_order');
		
		$this->data['button_save']        = $this->language->get('button_save');
		$this->data['button_cancel']      = $this->language->get('button_cancel');
        
		$this->data['tab_general']        = $this->language->get('tab_general');
        
        //Definitions lang SE
        $this->data['entry_sweden']        = $this->language->get('entry_sweden');
        $this->data['entry_username_SE']   = $this->language->get('entry_username_SE');
        $this->data['entry_password_SE']   = $this->language->get('entry_password_SE');
        $this->data['entry_clientno_SE']   = $this->language->get('entry_clientno_SE');
        $this->data['entry_invoicefee_SE'] = $this->language->get('entry_invoicefee_SE');
        $this->data['entry_invoicefee_usetax'] = $this->language->get('entry_invoicefee_usetax');
        
        //Definitions lang NL
        $this->data['entry_netherlands']   = $this->language->get('entry_netherlands');
        $this->data['entry_username_NL']   = $this->language->get('entry_username_NL');
        $this->data['entry_password_NL']   = $this->language->get('entry_password_NL');
        $this->data['entry_clientno_NL']   = $this->language->get('entry_clientno_NL');
        $this->data['entry_invoicefee_NL'] = $this->language->get('entry_invoicefee_NL');
        
        $this->data['entry_testmode']      = $this->language->get('entry_testmode');
        $this->data['entry_yes']           = $this->language->get('entry_yes');
        $this->data['entry_no']            = $this->language->get('entry_no');
        
        //Definitions settings SE
        $this->data['svea_invoice_username_SE']   = $this->config->get('svea_invoice_username_SE');
        $this->data['svea_invoice_password_SE']   = $this->config->get('svea_invoice_password_SE');
        $this->data['svea_invoice_clientno_SE']   = $this->config->get('svea_invoice_clientno_SE');
        $this->data['svea_invoicefee_SE']         = $this->config->get('svea_invoicefee_SE');
        $this->data['svea_invoicefee_usetax_SE']  = $this->config->get('svea_invoicefee_usetax_SE');
        
        //Definitions settings NL
        $this->data['svea_invoice_username_NL']   = $this->config->get('svea_invoice_username_NL');
        $this->data['svea_invoice_password_NL']   = $this->config->get('svea_invoice_password_NL');
        $this->data['svea_invoice_clientno_NL']   = $this->config->get('svea_invoice_clientno_NL');
        $this->data['svea_invoicefee_NL']         = $this->config->get('svea_invoicefee_NL');
        
        $this->data['svea_invoice_sort_order']    = $this->config->get('svea_invoice_sort_order');
        $this->data['svea_invoice_testmode']      = $this->config->get('svea_invoice_testmode');
        
        
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

  		$this->document->breadcrumbs = array();

   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=common/home&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		);

   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('text_payment'),
      		'separator' => ' :: '
   		);
		
   		$this->document->breadcrumbs[] = array(
       		'href'      => HTTPS_SERVER . 'index.php?route=payment/svea_invoice&token=' . $this->session->data['token'],
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);
        
		
		$this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/svea_invoice&token=' . $this->session->data['token'];

		$this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];	
		
        
        
		if (isset($this->request->post['svea_invoice_order_status_id'])) {
			$this->data['svea_invoice_order_status_id'] = $this->request->post['svea_invoice_order_status_id'];
		} else {
			$this->data['svea_invoice_order_status_id'] = $this->config->get('svea_invoice_order_status_id'); 
		} 
		
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['svea_invoice_geo_zone_id'])) {
			$this->data['svea_invoice_geo_zone_id'] = $this->request->post['svea_invoice_geo_zone_id'];
		} else {
			$this->data['svea_invoice_geo_zone_id'] = $this->config->get('svea_invoice_geo_zone_id'); 
		}
        
		
		$this->load->model('localisation/geo_zone');						
		
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
        
		if (isset($this->request->post['svea_invoice_status'])) {
			$this->data['svea_invoice_status'] = $this->request->post['svea_invoice_status'];
		} else {
			$this->data['svea_invoice_status'] = $this->config->get('svea_invoice_status');
		}
		
		if (isset($this->request->post['svea_invoice_sort_order'])) {
			$this->data['svea_invoice_sort_order'] = $this->request->post['svea_invoice_sort_order'];
		} else {
			$this->data['svea_invoice_sort_order'] = $this->config->get('svea_invoice_sort_order');
		}
        
        if (isset($this->request->post['svea_invoice_testmode'])) {
			$this->data['svea_invoice_testmode'] = $this->request->post['svea_invoice_testmode'];
		} else {
			$this->data['svea_invoice_testmode'] = $this->config->get('svea_invoice_testmode');
		}
		
		$this->template = 'payment/svea_invoice.tpl';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}
	
     public function sveaOrdersList(){
        
        //Load models and language definitions
        $this->load->language('sale/svea_order');
        $this->load->language('sale/order');
		$this->load->model('sale/order');
        $this->load->model('localisation/order_status');
        $this->load->model('setting/setting');

		//Language definitions
		$this->document->setTitle($this->language->get('heading_title'));
        $this->data['error_warning']             = (isset($this->error['warning'])) ? $this->error['warning'] : '';
        $this->data['deliver_all_btn']           = $this->language->get('deliver_all_btn');
        $this->data['view']                      = $this->language->get('view_order');
        $this->data['deliver_one_order']         = $this->language->get('deliver_one_order');
        $this->data['deliver_one_order_offline'] = $this->language->get('deliver_one_order_offline');
        
        
        //$this->template = 'payment/svea_deliver.tpl';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
        
        
        //Some actions
	    $this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/svea_invoice/sveaOrdersList&token=' . $this->session->data['token'];
		$this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];	
       
        //Get all the order statuses
        $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    	$this->getList();
        
        $this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}
    
    
    //Deliver multiple orders
    public function doDelivers(){

        if (isset($_GET['orderIds'])){
        
        $this->load->model('sale/order');
        
        foreach ($_GET['orderIds'] as $orderid){
            $order = $this->model_sale_order->getOrder($orderid);
            $orderTotal = $this->model_sale_order->getOrderTotals($orderid);
            $orderProducts = $this->model_sale_order->getOrderProducts($orderid);
            
            
            // DO THE DELIVER ORDER HERE
            
            print_r($order);
            //print_r($orderTotal);
            //print_r($orderProducts);
        }
        
        }
    }
    
    
    //Deliver order
    public function doDeliver(){

        if (isset($_GET['orderId'])){
        
        $this->load->model('sale/order');
        $orderid = $_GET['orderId'];

        $order = $this->model_sale_order->getOrder($orderid);
        $orderTotal = $this->model_sale_order->getOrderTotals($orderid);
        $orderProducts = $this->model_sale_order->getOrderProducts($orderid);
        
        
        // DO THE DELIVER ORDER HERE
        
        print_r($order);
        //print_r($orderTotal);
        //print_r($orderProducts);
        }
    }
    
    
    //Deliver order
    public function doDeliverOffline(){

        if (isset($_GET['orderId'])){
        
        $this->load->model('sale/order');
        $orderid = $_GET['orderId'];

        $order = $this->model_sale_order->getOrder($orderid);
        $orderTotal = $this->model_sale_order->getOrderTotals($orderid);
        $orderProducts = $this->model_sale_order->getOrderProducts($orderid);
        
        
        // DO THE DELIVER ORDER HERE
        
        print_r($order);
        //print_r($orderTotal);
        //print_r($orderProducts);
        }
    }
    
    
	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/svea_invoice')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
				
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
    
    private function getList() {
		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
			$filter_order_id = null;
		}

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = null;
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = null;
		}
		
		if (isset($this->request->get['filter_total'])) {
			$filter_total = $this->request->get['filter_total'];
		} else {
			$filter_total = null;
		}
		
		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = null;
		}
		
		if (isset($this->request->get['filter_date_modified'])) {
			$filter_date_modified = $this->request->get['filter_date_modified'];
		} else {
			$filter_date_modified = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.order_id';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
				
		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}
											
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
		
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}
					
		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}
		
		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['invoice'] = $this->url->link('sale/order/invoice', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['insert'] = $this->url->link('sale/order/insert', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['delete'] = $this->url->link('sale/order/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['orders'] = array();

		$data = array(
			'filter_order_id'        => $filter_order_id,
			'filter_customer'	     => $filter_customer,
			'filter_order_status_id' => $filter_order_status_id,
			'filter_total'           => $filter_total,
			'filter_date_added'      => $filter_date_added,
			'filter_date_modified'   => $filter_date_modified,
			'sort'                   => $sort,
			'order'                  => $order,
			'start'                  => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                  => $this->config->get('config_admin_limit')
		);

		$order_total = $this->model_sale_order->getTotalOrders($data);

		//$results = $this->model_sale_order->getOrders($data);
        $this->load->model('sale/svea_order');
        
        $results = $this->model_sale_svea_order->getOrdersSvea($data);
    	foreach ($results as $result) {
			$action = array();
					
			$action[] = array(
				'text' => $this->language->get('text_view'),
				'href' => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'] . $url, 'SSL')
			);
			
			if (strtotime($result['date_added']) > strtotime('-' . (int)$this->config->get('config_order_edit') . ' day')) {
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('sale/order/update', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'] . $url, 'SSL')
				);
			}
			
			$this->data['orders'][] = array(
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
				'status'        => $result['status'],
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'selected'      => isset($this->request->post['selected']) && in_array($result['order_id'], $this->request->post['selected']),
				'action'        => $action
			);
            
            $orderInfo = $this->model_sale_order->getOrder($result['order_id']);
            
            
		}
        
		$this->data['heading_title'] = 'SVEA Deliver Invoice';

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_missing'] = $this->language->get('text_missing');

		$this->data['column_order_id'] = $this->language->get('column_order_id');
    	$this->data['column_customer'] = $this->language->get('column_customer');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_date_modified'] = $this->language->get('column_date_modified');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_invoice'] = $this->language->get('button_invoice');
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_filter'] = $this->language->get('button_filter');

		$this->data['token'] = $this->session->data['token'];
		
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}
											
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
		
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}
					
		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}
		
		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['sort_order'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . '&sort=o.order_id' . $url, 'SSL');
		$this->data['sort_customer'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . '&sort=customer' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
		$this->data['sort_total'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . '&sort=o.total' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . '&sort=o.date_added' . $url, 'SSL');
		$this->data['sort_date_modified'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . '&sort=o.date_modified' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}
											
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
		
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}
					
		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}
		
		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/order', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['filter_order_id'] = $filter_order_id;
		$this->data['filter_customer'] = $filter_customer;
		$this->data['filter_order_status_id'] = $filter_order_status_id;
		$this->data['filter_total'] = $filter_total;
		$this->data['filter_date_added'] = $filter_date_added;
		$this->data['filter_date_modified'] = $filter_date_modified;

		$this->load->model('localisation/order_status');

    	$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'sale/svea_order_list.tpl';

		$this->response->setOutput($this->render());
  	}
}
?>