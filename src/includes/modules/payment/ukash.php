<?php

/**
 * -----------------------------------------------------------------------------------------
 * $Id: ukash.php  2013-07-17
 * @author Stefan Paul 
 * Ukash payment Zahlungshandler
 * BTCs immer willkommen:
 * 1MJ72T9iD4RYS4CXFEWJjVAE6tWbDdAv5u
 * Released under the GNU General Public License
 * --------------------------------------------------------------------------------------- */
include (DIR_FS_CATALOG . 'callback/ukash/UkashPayment.class.php');

class ukash {

    var $code, $title, $description, $enabled, $DataToPost;

    // class constructor
    function ukash() {
        global $order;
        $this->code = 'ukash';
        $this->title = MODULE_PAYMENT_UKASH_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_UKASH_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_PAYMENT_UKASH_SORT_ORDER;
        $this->info = MODULE_PAYMENT_UKASH_TEXT_INFO;
        $this->enabled = ((MODULE_PAYMENT_UKASH_STATUS == 'True') ? true : false);
        $this->form_action_url = MODULE_PAYMENT_UKASH_URL_REDIRECT;

		if (is_object($order))
            $this->update_status();
    }

    function update_status() {
        global $order, $xtPrice;
        ################################################################## START
        ## this is all for checking if the amount in SEK is high enough
        if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
            $total = $order->info['total'] + $order->info['tax'];
        } else {
            $total = $order->info['total'];
        }
        ## this is all for checking if the amount in SEK is high enough
        ################################################################## ENDE
    }

    // class methods
    function javascript_validation() {
        return false;
    }

    function selection() {
//        debug_ps($_SESSION['customers_status']['customers_status_name']);
        $content = array();
        $accepted = "<span class='paymentMap paymentMap_icons_ukash'></span>";
//        $accepted = '';
        $content = array_merge($content, array(
            array(
                'title' => ' ',
                'field' => $accepted
            )
        ));

        return array('id' => $this->code,
            'module' => $this->title,
            'module_cost' => $GLOBALS['ot_payment']->get_percent($this->code),
            'fields' => $content,
            'description' => $this->info
        );
    }

    function pre_confirmation_check() {
        return false;
    }

    function confirmation() {
        $confirmation = array('id' => $this->code,
            'title' => $this->title . ': ' . $this->check,
            'fields' => array(array('title' => MODULE_PAYMENT_UKASH_TEXT_DESCRIPTION)),
            'description' => $this->info);
        return $confirmation;
    }

	
    function process_button() {
        return false;
    }

    function before_process() {
        return false;
    }

    function payment_action() {
        global $order, $xtPrice, $insert_id;
        $amount = round($order->info['total'], $xtPrice->get_decimal_places($currency));
        if ($_SESSION['currency'] != MODULE_PAYMENT_UKASH_CURRENCY) {
            $currency = MODULE_PAYMENT_UKASH_CURRENCY;
            $amount = round($xtPrice->xtcCalculateCurrEx($order->info['total'], $currency), $xtPrice->get_decimal_places($currency));
        }
        
        $tranId = UkashPayment::getNewTransactionID($insert_id, $_SESSION['customer_id'], $amount, $this->makeDataToPost($insert_id));
        
        $sql = "INSERT INTO payment_ukash SET 
                                transaction_id='" . $tranId['utid'] . "',
                                securitytoken='" . $tranId['securityToken'] . "',
                                brandid ='" . $this->selectBrandidForCountry($_SESSION['language_code']) . "',
                                orders_id=" . $insert_id . ",
                                amount_EUR='" . $amount . "',
                                currency='" . $_SESSION['currency'] . "',
                                date_created=NOW(),
                                errCode ='999'";
        $result = xtc_db_query($sql);
        if (!$result) {
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_CONFIRMATION, 'payment_error=' . $this->code . "&errorcode=233", 'SSL', true));
        }

        $paymenturl = MODULE_PAYMENT_UKASH_URL_REDIRECT . '?utid=' . $tranId['utid'];
        xtc_redirect($paymenturl);
    }

    function after_process() {
        return false;
    }

    function output_error() {
        return false;
    }

    function check() {
        if (!isset($this->check)) {
            $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_UKASH_STATUS'");
            $this->check = xtc_db_num_rows($check_query);
        }
        return $this->check;
    }
    function selectBrandidForCountry($countrycode){
        //MODULE_PAYMENT_UKASH_BRANDID to array
        $brandid2d = explode(',',MODULE_PAYMENT_UKASH_BRANDID);
        $brandid3d = array();
        foreach($brandid2d as $brandid){
            $tmp = explode('=', $brandid);
            $brandid3d[$tmp[0]] = $tmp[1];
        }
        if(isset($brandid3d[$countrycode])){
            return $brandid3d[$countrycode];
        }
        return $brandid3d[MODULE_PAYMENT_UKASH_BRANDID_DEFAULT_COUNTRY];
    }

    function makeDataToPost($insert_id) {
        // 'LanguageCode' => 'EN' // no other language supported
        
        $DataToPost = array('SecurityToken' => MODULE_PAYMENT_UKASH_SECURITY_TOKEN,
            'BrandID' => $this->selectBrandidForCountry($_SESSION['language_code']),
            'LanguageCode' => 'EN',
            'MerchantCurrency' => MODULE_PAYMENT_UKASH_CURRENCY,
            'URL_Success' => MODULE_PAYMENT_UKASH_URL_SUCCESS . '?sellid='.$insert_id ,
            'URL_Fail' => MODULE_PAYMENT_UKASH_URL_FAIL . '?sellid='.$insert_id,
            'URL_Notification' => MODULE_PAYMENT_UKASH_URL_NOTIFICATON
        );
        return $DataToPost;
    }

    function install() {
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_ALLOWED', '', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_UKASH_STATUS', 'True', '6', '3', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now());");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_BRANDID', '',  '6', '1', now());");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_BRANDID_DEFAULT_COUNTRY', '12345678901234567890', '6', '1', now());");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_SECURITY_TOKEN', '12345678901234567890', '6', '1', now());");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_CURRENCY', 'EUR',  '6', '1', now());");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_URL_SUCCESS', 'https://direct.staging.ukash.com/candystore/success.aspx',  '6', '1', now());");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_URL_FAIL', 'https://direct.staging.ukash.com/candystore/failure.aspx',  '6', '1', now());");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_URL_NOTIFICATON', 'http://direct.staging.ukash.com/candystore/Notification.aspx',  '6', '1', now());");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_SORT_ORDER', '0',  '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_URL_REDIRECT', 'https://direct.staging.ukash.com/hosted/entry.aspx',  '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_URL_ACTION', 'https://processing.staging.ukash.com/RPPGateway/process.asmx/GetTransactionStatus',  '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_TMP_STATUS_ID', '12',  '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_UKASH_CALLBACK_URL', 'http://SERVER.DOMAIN/callback/ukash/callback.php',  '6', '0', now())");
        
        // CALLBACK Table
        xtc_db_query("CREATE TABLE `payment_ukash` (
                        `transaction_id` varchar(20) NOT NULL,
                        `securitytoken` varchar(20) DEFAULT NULL,
                        `orders_id` int(11) NOT NULL,
                        `amount_eur` double(9,2) NOT NULL,
                        `currency` varchar(3) NOT NULL,
                        `transactionCode` smallint(6) DEFAULT NULL,
                        `transactionDesc` varchar(20) DEFAULT NULL,
                        `settleAmount` double(9,2) DEFAULT NULL,
                        `merchantCurrency` varchar(3) DEFAULT NULL,
                        `ukashTransactionID` varchar(25) DEFAULT NULL,
                        `errCode` smallint(5) unsigned DEFAULT NULL,
                        `errDescription` varchar(160) DEFAULT NULL,
                        `lastmodifed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        `date_created` datetime DEFAULT NULL,
                        PRIMARY KEY (`transaction_id`)
                      )");
    }

    /**
     * .paymentMap_icons_ukash {
     * 	background-position: -59px -379px;
     * 	width: 75px;
     * 	height: 40px;
     * }
     */
    
    
    function remove() {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
        $keys = array('MODULE_PAYMENT_UKASH_STATUS',
            'MODULE_PAYMENT_UKASH_ALLOWED',
            'MODULE_PAYMENT_UKASH_BRANDID',
            'MODULE_PAYMENT_UKASH_BRANDID_DEFAULT_COUNTRY',
            'MODULE_PAYMENT_UKASH_SECURITY_TOKEN',
            'MODULE_PAYMENT_UKASH_CURRENCY',
            'MODULE_PAYMENT_UKASH_URL_SUCCESS',
            'MODULE_PAYMENT_UKASH_URL_FAIL',
            'MODULE_PAYMENT_UKASH_URL_NOTIFICATON',
            'MODULE_PAYMENT_UKASH_SORT_ORDER',
            'MODULE_PAYMENT_UKASH_URL_REDIRECT',
            'MODULE_PAYMENT_UKASH_TMP_STATUS_ID',
            'MODULE_PAYMENT_UKASH_URL_ACTION',
            'MODULE_PAYMENT_UKASH_CALLBACK_URL');

        return $keys;
    }

}

?>