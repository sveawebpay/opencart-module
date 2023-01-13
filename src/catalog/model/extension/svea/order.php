<?php

class ModelExtensionSveaOrder extends Model
{
    public function getOrderHistoryComment($orderId)
    {
        return $this->db->query("SELECT comment FROM `" . DB_PREFIX . "order_history` WHERE order_id = " . $orderId);
    }
}
