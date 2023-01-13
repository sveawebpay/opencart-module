<?php

class ModelExtensionSveaUpgrade extends Model
{
    private $userTokenString = "user_";
    private $linkString = "marketplace/extension";
    private $paymentString ="payment_";
    private $moduleString = "module_";
    private $appendString = "_before";

    private function setVersionStrings()
    {
        if (VERSION < 3.0) {
            $this->userTokenString = "";
            $this->linkString = "extension/extension";
            $this->paymentString = "";
            $this->moduleString = "";
            $this->appendString = "";
        }
    }

    public function upgradeDatabase($module)
    {
        $this->setVersionStrings();

        $this->db->query(' CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'svea_version`
                (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `module` VARCHAR( 100 ) NOT NULL,
                `version` INT NOT NULL
            )   ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
            ');

        $versionQuery = $this->db->query("SELECT version FROM `" . DB_PREFIX . "svea_version` WHERE module = '$module'");

        if ($versionQuery->num_rows == 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "svea_version` SET module = '$module', version = 0");
            $version = 0;
        } else {
            $version = $versionQuery->row['version'];
        }

        if ($version < 1) {
            switch ($module) {
                case 'svea_partpayment':
                    $tableExists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "svea_params_table'");
                    if ($tableExists->num_rows) {
                        $this->db->query("ALTER TABLE `" . DB_PREFIX . "svea_params_table` MODIFY interestRatePercent DOUBLE NOT NULL  ");
                    }
                    break;
                case 'sco':
                    $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . $this->moduleString .'sco_campaigns`
                        (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        `campaignCode` VARCHAR( 100 ) NOT NULL,
                        `contractLengthInMonths` INT NOT NULL ,
                        `description` VARCHAR( 100 ) NOT NULL ,
                        `fromAmount` DOUBLE NOT NULL ,
                        `initialFee` DOUBLE NOT NULL ,
                        `interestRatePercent` DOUBLE NOT NULL ,
                        `monthlyAnnuityFactor` DOUBLE NOT NULL ,
                        `notificationFee` DOUBLE NOT NULL ,
                        `numberOfInterestFreeMonths` INT NOT NULL ,
                        `numberOfPaymentFreeMonths` INT NOT NULL ,
                        `paymentPlanType` VARCHAR( 100 ) NOT NULL ,
                        `toAmount` DOUBLE NOT NULL ,
                        `timestamp` INT UNSIGNED NOT NULL,
                        `countryCode` VARCHAR( 100 ) NOT NULL,
                        `productionEnvironment` INT NOT NULL
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
                    $this->db->query("ALTER TABLE " . DB_PREFIX . $this->moduleString . "sco_campaigns MODIFY interestRatePercent DOUBLE NOT NULL  ");
                    $tableExists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "order_sco'");
                    if ($tableExists->num_rows) {
                        $this->db->query("ALTER TABLE " . DB_PREFIX . "order_sco ADD COLUMN newsletter boolean DEFAULT 0");
                    }
                    break;
            }
            $this->db->query("UPDATE " . DB_PREFIX . "svea_version SET version=1 WHERE module='$module'");
        }
        if ($version < 2) {
            switch ($module) {
                case 'svea_partpayment':
                    $tableExists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "svea_params_table'");
                    if ($tableExists->num_rows) {
                        $tableExists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "svea_wp_campaigns'");
                        if (!$tableExists->num_rows) {
                            $this->db->query("ALTER TABLE `" . DB_PREFIX . "svea_params_table` RENAME TO `" . DB_PREFIX . "svea_wp_campaigns`");
                        }
                    }
                    break;

                case 'sco':
                    $tableExists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "order_sco'");
                    if ($tableExists->num_rows) {
                        $tableExists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "svea_sco_order'");
                        if (!$tableExists->num_rows) {
                            $this->db->query("ALTER TABLE " . DB_PREFIX . "order_sco RENAME TO " . DB_PREFIX . "svea_sco_order");
                        }
                    }
                    $tableExists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . $this->moduleString . "sco_campaigns'");
                    if ($tableExists->num_rows) {
                        $tableExists = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "svea_sco_campaigns'");
                        if (!$tableExists->num_rows) {
                            $this->db->query("ALTER TABLE " . DB_PREFIX . $this->moduleString . "sco_campaigns RENAME TO " . DB_PREFIX . "svea_sco_campaigns");
                        }
                    }
                    break;
            }
            $this->db->query("UPDATE " . DB_PREFIX . "svea_version SET version=2 WHERE module='$module'");
        }
    }
}
