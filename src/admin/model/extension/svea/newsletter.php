<?php

class ModelExtensionSveaNewsletter extends Model
{
    public function getUsersConsentingToNewsletter()
    {
        $query = $this->db->query("SELECT DISTINCT " . DB_PREFIX . "order.email FROM " . DB_PREFIX . "order INNER JOIN " . DB_PREFIX . "order_sco ON " . DB_PREFIX . "order.order_id=" . DB_PREFIX . "order_sco.order_id WHERE " . DB_PREFIX . "order.email != '' AND " . DB_PREFIX . "order_sco.newsletter != 0;");
        return $query->rows;
    }
}