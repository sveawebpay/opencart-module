<?php

class ModelExtensionSveaCampaigns extends Model
{
    //backwards compatability
    private $userTokenString = "user_";
    private $linkString = "marketplace/extension";
    private $paymentString ="payment_";
    private $moduleString = "module_";
    private $appendString = "_before";
    private $eventString = "setting/event";

    private function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->userTokenString = "";
            $this->linkString = "extension/extension";
            $this->paymentString = "";
            $this->moduleString = "";
            $this->appendString = "";
            $this->eventString = "extension/event";
        }
    }

    public function createScoCampaignsTableIfNotExist()
    {
        $this->setVersionStrings();
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
            )   ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
            ');
    }

    public function truncateScoCampaignsTable()
    {
        $this->setVersionStrings();
        $this->db->query('TRUNCATE TABLE ' . DB_PREFIX . $this->moduleString .'sco_campaigns');
    }

    public function fetchScoCountries($countryString)
    {
        $countries = $this->db->query("SELECT `key`, `value` FROM " . DB_PREFIX . "setting WHERE `key` LIKE " . $countryString . ";");
        return $countries;
    }

    public function insertScoCampaignsToTable($response, $country)
    {
        $this->setVersionStrings();
        foreach ($response as $responseResultItem)
        {
            try
            {
                $campaignCode = (isset($responseResultItem['CampaignCode'])) ? $responseResultItem['CampaignCode'] : "";
                $description = (isset($responseResultItem['Description'])) ? $responseResultItem['Description'] : "";
                $paymentPlanType = (isset($responseResultItem['PaymentPlanType'])) ? $responseResultItem['PaymentPlanType'] : "";
                $contractLength = (isset($responseResultItem['ContractLengthInMonths'])) ? $responseResultItem['ContractLengthInMonths'] : "";
                $monthlyAnnuityFactor = (isset($responseResultItem['MonthlyAnnuityFactor'])) ? $responseResultItem['MonthlyAnnuityFactor'] : "";
                $initialFee = (isset($responseResultItem['InitialFee'])) ? $responseResultItem['InitialFee'] : "";
                $notificationFee = (isset($responseResultItem['NotificationFee'])) ? $responseResultItem['NotificationFee'] : "";
                $interestRatePercentage = (isset($responseResultItem['InterestRatePercent'])) ? $responseResultItem['InterestRatePercent'] : "";
                $interestFreeMonths = (isset($responseResultItem['NumberOfInterestFreeMonths'])) ? $responseResultItem['NumberOfInterestFreeMonths'] : "";
                $paymentFreeMonths = (isset($responseResultItem['NumberOfPaymentFreeMonths'])) ? $responseResultItem['NumberOfPaymentFreeMonths'] : "";
                $fromAmount = (isset($responseResultItem['FromAmount'])) ? $responseResultItem['FromAmount'] : "";
                $toAmount = (isset($responseResultItem['ToAmount'])) ? $responseResultItem['ToAmount'] : "";

                try
                {
                    $this->db->query("INSERT INTO " . DB_PREFIX . $this->moduleString ."sco_campaigns SET
                                    campaignCode = '" . $this->db->escape($campaignCode) . "',
                                    contractLengthInMonths = '" . $this->db->escape($contractLength) . "',
                                    description = '" . $this->db->escape($description) . "',
                                    fromAmount = '" . $this->db->escape($fromAmount) . "',
                                    initialFee = '" . $this->db->escape($initialFee) . "',
                                    interestRatePercent = '" . $this->db->escape($interestRatePercentage) . "',
                                    monthlyAnnuityFactor = '" . $this->db->escape($monthlyAnnuityFactor) . "',
                                    notificationFee = '" . $this->db->escape($notificationFee) . "',
                                    numberOfInterestFreeMonths = '" . $this->db->escape($interestFreeMonths) . "',
                                    numberOfPaymentFreeMonths = '" . $this->db->escape($paymentFreeMonths) . "',
                                    paymentPlanType = '" . $this->db->escape($paymentPlanType) . "',
                                    toAmount = '" . $this->db->escape($toAmount) . "',
                                    timestamp = '" . $this->db->escape(time()) . "',
                                    countryCode = '" . $this->db->escape(strtoupper(substr($country['key'], -2))) . "'");
                }
                catch (Exception $e)
                {
                    $this->log->write($e->getMessage());
                }
            }
            catch (Exception $e)
            {
                $this->log->write($e->getMessage());
            }
        }
    }

    public function checkIfOrderScoTableExists()
    {
        $query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "order_sco'");
        return $query;
    }

    public function createOrderScoTable()
    {
        $this->db->query("CREATE TABLE `" . DB_PREFIX . "order_sco` (
                                `order_id`				int(11) unsigned NOT NULL AUTO_INCREMENT,
                                `checkout_id`           int(11) unsigned DEFAULT NULL, 
                                `locale` 				varchar(10) DEFAULT NULL,
                                `country` 				varchar(8) DEFAULT NULL,
                                `currency` 				varchar(4) DEFAULT NULL, 
                                `status` 				varchar(30) DEFAULT NULL,
                                `type` 					varchar(20) DEFAULT NULL, 
                                `notes` 	        	text DEFAULT NULL,
                                `newsletter` 	        bool DEFAULT 0,  
            					`date_added` 			datetime DEFAULT NULL, 
            					`date_modified` 		datetime DEFAULT NULL,
                              PRIMARY KEY (`order_id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8; ");
    }

    public function truncateSveaParamsTable()
    {
        $this->db->query('TRUNCATE TABLE ' . DB_PREFIX . 'svea_params_table');
    }

    public function createSveaParamsTable()
    {
        $this->db->query(' CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'svea_params_table`
                (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `campaignCode` VARCHAR( 100 ) NOT NULL,
                `description` VARCHAR( 100 ) NOT NULL ,
                `paymentPlanType` VARCHAR( 100 ) NOT NULL ,
                `contractLengthInMonths` INT NOT NULL ,
                `monthlyAnnuityFactor` DOUBLE NOT NULL ,
                `initialFee` DOUBLE NOT NULL ,
                `notificationFee` DOUBLE NOT NULL ,
                `interestRatePercent` DOUBLE NOT NULL ,
                `numberOfInterestFreeMonths` INT NOT NULL ,
                `numberOfPaymentFreeMonths` INT NOT NULL ,
                `fromAmount` DOUBLE NOT NULL ,
                `toAmount` DOUBLE NOT NULL ,
                `timestamp` INT UNSIGNED NOT NULL,
                `countryCode` VARCHAR( 100 ) NOT NULL
            )   ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
            ');
    }

    public function insertSveaCampaignsToTable($params, $countryCode)
    {

        $error_flag = false;

        foreach ($params as $param) {
            //$query = $db->getQuery(true);
            $q = "INSERT INTO `" . DB_PREFIX . "svea_params_table`
                    (   `campaignCode` ,
                        `description`,
                        `paymentPlanType`,
                        `contractLengthInMonths`,
                        `monthlyAnnuityFactor`,
                        `initialFee`,
                        `notificationFee`,
                        `interestRatePercent`,
                        `numberOfInterestFreeMonths`,
                        `numberOfPaymentFreeMonths`,
                        `fromAmount`,
                        `toAmount`,
                        `timestamp`,
                        `countryCode`)
                        VALUES(";
            foreach ($param as $key => $value)
                $q .= "'" . $value . "',";

            $q .= time() . ",";
            $q .= "'" . $countryCode . "'";
            $q .= ")";
            try {
                $this->db->query($q);
            } catch (Exception $e) {
                $this->log->write("Failed to update PaymentPlanParams " . $e->getMessage());
                $error_flag = true;
            }
        }

        if ($error_flag == false) {
            $this->log->write("Successfully updated PaymentPlanParams");
        }
    }
}