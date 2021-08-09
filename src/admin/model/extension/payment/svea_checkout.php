<?php

class ModelExtensionPaymentSveaCheckout extends Model
{
    public function findCountryByCode(array $data)
    {
        if (empty($data)) { return; }

        $codes = sprintf('\'%s\'', implode('\',\'', $data));

        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "country WHERE iso_code_2 IN (" . $codes . ") AND status = 1");

        return $query->rows;
    }

    public function install()
    {
        //
    }

    public function uninstall()
    {
        //
    }

    public function upgrade(string $version)
    {
        $this->load->model('setting/setting');

        if (!$this->config->get('payment_svea_checkout_upgrade_version')) {
            $this->delete_files();
            $this->convert_settings();
        }

        $this->model_setting_setting->editSettingValue('payment_svea_checkout', 'payment_svea_checkout_upgrade_version', $version);
    }

    public function delete_files()
    {
        $files = [
            DIR_APPLICATION . 'controller/extension/module/sco.php',
            DIR_APPLICATION . 'controller/extension/payment/sco.php',
            DIR_APPLICATION . 'language/da-dk/extension/payment/sco.php',
            DIR_APPLICATION . 'language/de-de/extension/payment/sco.php',
            DIR_APPLICATION . 'language/en-gb/extension/payment/sco.php',
            DIR_APPLICATION . 'language/fi-fi/extension/payment/sco.php',
            DIR_APPLICATION . 'language/nn-no/extension/payment/sco.php',
            DIR_APPLICATION . 'language/sv-se/extension/payment/sco.php',
            DIR_APPLICATION . 'view/template/extension/module/sco.twig',
            DIR_APPLICATION . 'view/template/extension/payment/sco.twig',

            DIR_CATALOG . 'controller/extension/payment/sco.php',
            DIR_CATALOG . 'language/da-dk/extension/payment/sco.php',
            DIR_CATALOG . 'language/de-de/extension/payment/sco.php',
            DIR_CATALOG . 'language/en-gb/extension/payment/sco.php',
            DIR_CATALOG . 'language/fi-fi/extension/payment/sco.php',
            DIR_CATALOG . 'language/nn-no/extension/payment/sco.php',
            DIR_CATALOG . 'language/sv-se/extension/payment/sco.php',
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }

    private function convert_settings()
    {
        $keys = [
            'module_sco_checkout_default_country_id'  => 'payment_svea_checkout_default_country',
            'module_sco_test_mode'                    => 'payment_svea_checkout_test_mode',
            'module_sco_status_checkout'              => 'payment_svea_checkout_status',

            'module_sco_checkout_test_merchant_id_se' => 'payment_svea_checkout_test_se_merchant_id',
            'module_sco_checkout_test_secret_word_se' => 'payment_svea_checkout_test_se_secret_key',
            'module_sco_checkout_merchant_id_se'      => 'payment_svea_checkout_live_se_merchant_id',
            'module_sco_checkout_secret_word_se'      => 'payment_svea_checkout_live_se_secret_key',

            'module_sco_checkout_test_merchant_id_no' => 'payment_svea_checkout_test_no_merchant_id',
            'module_sco_checkout_test_secret_word_no' => 'payment_svea_checkout_test_no_secret_key',
            'module_sco_checkout_merchant_id_no'      => 'payment_svea_checkout_live_no_merchant_id',
            'module_sco_checkout_secret_word_no'      => 'payment_svea_checkout_live_no_secret_key',

            'module_sco_checkout_test_merchant_id_fi' => 'payment_svea_checkout_test_fi_merchant_id',
            'module_sco_checkout_test_secret_word_fi' => 'payment_svea_checkout_test_fi_secret_key',
            'module_sco_checkout_merchant_id_fi'      => 'payment_svea_checkout_live_fi_merchant_id',
            'module_sco_checkout_secret_word_fi'      => 'payment_svea_checkout_live_fi_secret_key',

            'module_sco_checkout_test_merchant_id_dk' => 'payment_svea_checkout_test_dk_merchant_id',
            'module_sco_checkout_test_secret_word_dk' => 'payment_svea_checkout_test_dk_secret_key',
            'module_sco_checkout_merchant_id_dk'      => 'payment_svea_checkout_live_dk_merchant_id',
            'module_sco_checkout_secret_word_dk'      => 'payment_svea_checkout_live_dk_secret_key',

            'module_sco_checkout_test_merchant_id_de' => 'payment_svea_checkout_test_de_merchant_id',
            'module_sco_checkout_test_secret_word_de' => 'payment_svea_checkout_test_de_secret_key',
            'module_sco_checkout_merchant_id_de'      => 'payment_svea_checkout_live_de_merchant_id',
            'module_sco_checkout_secret_word_de'      => 'payment_svea_checkout_live_de_secret_key',
        ];

        foreach ($keys as $key => $new_key) {
            $this->db->query("UPDATE " . DB_PREFIX . "setting SET `code` = 'payment_svea_checkout', `key` = '" . $new_key . "' WHERE `key` = '" . $key . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `code` = 'module_sco'");

        return true;
    }
}
