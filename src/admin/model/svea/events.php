<?php

class ModelSveaEvents extends Model
{
    public function deleteSveaCustomEvents()
    {
        $this->load->model('extension/event');
        $payment_methods = $this->getExtensions('payment');

        $svea_active_payments_count = 0;
        $sco_active = false;

        foreach ($payment_methods as $payment_method) {
            if ($this->config->get($payment_method['code'] . '_status')) {
                $payment_code = $payment_method['code'];
                if (strpos($payment_code, 'svea') !== false) {
                    $svea_active_payments_count++;
                } else if ($payment_code === 'sco') {
                    $sco_active = true;
                }
            }
        }

        if ($svea_active_payments_count === 0 && $sco_active === false) {
            $this->model_extension_event->deleteEvent('sco_edit_order_from_admin_before');
        }
    }

    public function addSveaCustomEvent($code, $trigger, $action)
    {
        $this->load->model('extension/event');
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "event WHERE `code` = '" . $this->db->escape($code) . "'");

        if (count($query->rows) === 0) {
            $this->model_extension_event->addEvent($code, $trigger, $action);
        }
    }

    private function getExtensions($type)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = '" . $this->db->escape($type) . "'");

        return $query->rows;
    }
}