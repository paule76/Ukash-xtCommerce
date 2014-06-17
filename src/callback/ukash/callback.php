<?php

/**
 * Ukash payment Zahlungsoption
 * BTCs immer willkommen:
 * 1MJ72T9iD4RYS4CXFEWJjVAE6tWbDdAv5u
 * @author Stefan Paul
 */
error_reporting(E_ALL);
include ('../../includes/application_top_callback.php');
include ('./UkashPayment.class.php');

function getConfiguration() {
    $sql = "SELECT configuration_key, configuration_value FROM `" . TABLE_CONFIGURATION . "` WHERE configuration_key LIKE 'MODULE_PAYMENT_UKASH%';";
    $result = xtc_db_query($sql);
    $config = array();
    while ($row = xtc_db_fetch_array($result)) {
        $config[$row['configuration_key']] = $row['configuration_value'];
    }
	return $config;
}

function writeIncomingAction($uTID) {
    $sql = "UPDATE payment_ukash SET errCode=998 WHERE transaction_id = '" . mysql_escape_string($uTID) . "'";
    return xtc_db_query($sql);
}

function writeTransactionsDetails($transdetails) {
    $sql = "    UPDATE payment_ukash
                SET transactionCode = " . $transdetails['transactionCode'] . ",
                 transactionDesc = '" . $transdetails['transactionDesc'] . "',
                 settleAmount = '" . $transdetails['settleAmount'] . "',
                 merchantCurrency = '" . $transdetails['merchantCurrency'] . "',
                 ukashTransactionID = '" . $transdetails['ukashTransactionID'] . "',
                 errCode = '" . $transdetails['errorid'] . "',
                 errDescription = '" . $transdetails['errDescription'] . "'
                WHERE transaction_id = '" . $transdetails['uTID'] . "'";
    $result = xtc_db_query($sql);
    return $result;
}


function getOrderIdUkash($uTID) {
    $sql = "SELECT orders_id FROM payment_ukash WHERE transaction_id = '" . $uTID . "' LIMIT 1";
    $result = xtc_db_query($sql);
    $id = xtc_db_fetch_array($result);
    return $id['orders_id'];
}

function doesOrderExists($orderId) {
    $sql = "SELECT 'TRUE' AS isDa FROM orders WHERE orders_id = " . $orderId;
    $result = xtc_db_query($sql);
    $id = xtc_db_fetch_array($result);
    return ($id['isDa'] == TRUE);
}

function setOrderAsPayed($orderId ) {
    $sql = "UPDATE orders o , orders_status_history osh, payment_ukash p
            SET o.orders_status = 2, osh.orders_status_id = 2
            WHERE p.transactionCode = 0
            AND p.orders_id = o.orders_id
            AND o.orders_id = osh.orders_id
            AND o.orders_status != 2
            AND o.orders_id = " . $orderId;
    $result = xtc_db_query($sql);
    return $result;
}

function isTransactionExists($uTID){
    $sql = "SELECT  'TRUE' AS isDa FROM payment_ukash WHERE transaction_id = '".$uTID."'";
    $result = xtc_db_query($sql);
    $id = xtc_db_fetch_array($result);
    return ($id['isDa'] == TRUE);
    
}
function getTransactionBrandId($uTID){
    $sql = "SELECT  brandid FROM payment_ukash WHERE transaction_id = '".$uTID."'";
    $result = xtc_db_query($sql);
    $id = xtc_db_fetch_array($result);
    return $id['brandid'];
    }

function getUtidFromSellId($sellid){
    $sql = "SELECT transaction_id AS tid FROM payment_ukash WHERE orders_id = ".$sellid;
    $result = xtc_db_query($sql);
    $id = xtc_db_fetch_array($result);
    return $id['tid'];
}

if (isset($_REQUEST['sellid'])) {
	$uTID = getUtidFromSellId(mysql_escape_string($_REQUEST['sellid']));
	writeIncomingAction($uTID);
}elseif (isset($_REQUEST['UTID'])) {
	$uTID = $_REQUEST['UTID'];
	writeIncomingAction($_REQUEST['UTID']);
}else{
	exit;
}

$config = getConfiguration();
$transaction = UkashPayment::checkTransaction($config['MODULE_PAYMENT_UKASH_SECURITY_TOKEN'], getTransactionBrandId($uTID), $uTID, $config['MODULE_PAYMENT_UKASH_URL_ACTION']);

if (isset($transaction['transactionCode']) && isTransactionExists($uTID)) {
    writeTransactionsDetails($transaction);
} else {
    echo $transaction['transactionCode'];
    exit();
}

if (isset($transaction['transactionCode']) && $transaction['transactionCode'] == 0) {
    $realOrderId = getOrderIdUkash($uTID);
    if ($realOrderId == $transaction['order_id'] && doesOrderExists($realOrderId)) {
        if (setOrderAsPayed($realOrderId)) {
            header("HTTP/1.0 200 OK", $deb);
        } 
    }
}

echo $transaction['transactionCode'];
exit;
?>