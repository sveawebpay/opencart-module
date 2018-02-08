<?php

class ModelExtensionSveaEvents extends Model
{
    public function deleteSveaCustomEvents()
    {
        $this->load->model('setting/event');
        $payment_methods = $this->getExtensions('payment');

        $svea_active_payments_count = 0;
        $module_sco_active = false;

        foreach ($payment_methods as $payment_method) {
            if ($this->config->get($payment_method['code'] . '_status')) {
                $payment_code = $payment_method['code'];
                if (strpos($payment_code, 'payment_svea') !== false) {
                    $svea_active_payments_count++;
                } else if ($payment_code === 'sco') {
                    $module_sco_active = true;
                }
            }
        }

        if ($svea_active_payments_count === 0 && $module_sco_active === false) {
            $this->model_setting_event->deleteEvent('module_sco_edit_order_from_admin_before');
            $this->model_setting_event->deleteEvent('module_sco_add_history_order_from_admin');
        }
    }

    public function addSveaCustomEvent($code, $trigger, $action)
    {
        $this->load->model('setting/event');
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "event WHERE `code` = '" . $this->db->escape($code) . "'");

        if (count($query->rows) === 0) {
            $this->model_setting_event->addEvent($code, $trigger, $action);
        }
    }

    private function getExtensions($type)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = '" . $this->db->escape($type) . "'");

        return $query->rows;
    }
}