<?php

class ModelExtensionSveaEvents extends Model
{
    private $userTokenString = "user_";
    private $linkString = "marketplace/extension";
    private $paymentString ="payment_";
    private $moduleString = "module_";
    private $appendString = "_before";
    private $eventString = "setting/event";

    public function setVersionStrings()
    {
        if (VERSION < 3.0) {
            $this->userTokenString = "";
            $this->linkString = "extension/extension";
            $this->paymentString = "";
            $this->moduleString = "";
            $this->appendString = "";
            $this->eventString = "extension/event";
        }
    }

    public function deleteSveaCustomEvents()
    {
        $this->setVersionStrings();
        $this->load->model($this->eventString);
        $payment_methods = $this->getExtensions('payment');

        $svea_active_payments_count = 0;
        $module_sco_active = false;

        foreach ($payment_methods as $payment_method) {
            if ($this->config->get($payment_method['code'] . '_status')) {
                $payment_code = $payment_method['code'];
                if (strpos($payment_code, $this->paymentString . 'svea') !== false) {
                    $svea_active_payments_count++;
                } elseif ($payment_code === 'sco') {
                    $module_sco_active = true;
                }
            }
        }

        if ($svea_active_payments_count === 0 && $module_sco_active === false) {
            if (VERSION < 3.0) {
                $this->model_extension_event->deleteEvent($this->paymentString . 'sco_edit_order_from_admin' . $this->appendString);
                $this->model_extension_event->deleteEvent($this->paymentString . 'sco_add_history_order_from_admin'. $this->appendString);
            } else {
                $this->model_setting_event->deleteEvent($this->paymentString . 'sco_edit_order_from_admin' . $this->appendString);
                $this->model_setting_event->deleteEvent($this->paymentString . 'sco_add_history_order_from_admin' . $this->appendString);
            }
        }
    }

    public function addSveaCustomEvent($code, $trigger, $action)
    {
        $this->setVersionStrings();

        $this->load->model($this->eventString);

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($code) . "'");

        if (count($query->rows) === 0) {
            if (VERSION < 3.0) {
                $this->model_extension_event->addEvent($code, $trigger, $action);
            } else {
                $this->model_setting_event->addEvent($code, $trigger, $action);
            }
        }
    }

    private function getExtensions($type)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = '" . $this->db->escape($type) . "'");

        return $query->rows;
    }
}
