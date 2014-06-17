<?php

/**
 * Ukash payment Zahlungshandler
 * BTCs immer willkommen:
 * 1MJ72T9iD4RYS4CXFEWJjVAE6tWbDdAv5u
 * @author Stefan Paul
 */
class UkashPayment {

    private static function DoHttpPost($URL, $ArrayOfPostData) {
        /**
         * Building QUERY (Including URL Encode)
         * or $postData = urlencode('s_Request='.$xml);
         */
        $postData = http_build_query($ArrayOfPostData);

        /**
         * Preparing Header
         * strlen | mb_strlen
         */
        $headA = array();
        $headA[] = "Content-Type: application/x-www-form-urlencoded";
        $headA[] = "Content-Length: " . mb_strlen($postData);

        /**
         * POST using CURL
         */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headA);
        $responseA = curl_exec($ch);
        curl_close($ch);

        /**
         * OUTPUT
         */
        return $responseA;
    }

    private static function url_decode($string) {
        return utf8_decode(urldecode($string));
    }

    /**
     * Erstellt eine Transaktions Id für die weitere Handhabung bei Ukash
     * @param type $MerchantTransactionID 
     * A unique reference to the transaction must be supplied by the merchant. If 
     * @param type $ConsumerID
     * Unique reference per consumer. Info is required for fraud analysis
     * @param type $TransactionValue
     * Value of the payment as required by the merchant. It is presented in 2 decimal points in the merchant base currency.
     * @return type String
     * 
     */
    public static function getNewTransactionID($MerchantTransactionID, $ConsumerID, $TransactionValue, $DataToPost = NULL) {
        //build up an array of the data to post to get TransactionID

        if (is_null($DataToPost)) {
            $DataToPost = array('SecurityToken' => '12345678901234567890',
                'BrandID' => 'UKASH10082',
                'LanguageCode' => 'De',
                'MerchantTransactionID' => '' . $MerchantTransactionID . '',
                'MerchantCurrency' => 'EUR',
                'ConsumerID' => '' . $ConsumerID . '',
                'URL_Success' => 'https://direct.staging.ukash.com/candystore/success.aspx',
                'URL_Fail' => 'https://direct.staging.ukash.com/candystore/failure.aspx',
                'URL_Notification' => 'http://direct.staging.ukash.com/candystore/Notification.aspx',
                'TransactionValue' => '' . $TransactionValue . '');
        } else {
            $DataToPostPart = array(
                'MerchantTransactionID' => '' . $MerchantTransactionID . '',
                'ConsumerID' => '' . $ConsumerID . '',
                'TransactionValue' => '' . $TransactionValue . '');
            $DataToPost = array_merge($DataToPost, $DataToPostPart);
        }

        //Post call to the RPP Gateway, an soap request will be returned.
        $XmlResult = (self::DoHttpPost('https://processing.staging.ukash.com/RPPGateway/Process.asmx/GetUniqueTransactionID', $DataToPost));
        //Convert the string value to XML
        $xml = new SimpleXmlElement($XmlResult);

        //Decode the xml strings value
        $decodedstring = self::url_decode($xml);

        //Reloaded the decoded string, as XML.
        $xml = new SimpleXmlElement($decodedstring);

        //Extract the UTID from the XML object
        $nodes = $xml->xpath('/UKashRPP/UTID');
        $UTID = (string) $nodes[0];

        $nodes = $xml->xpath('/UKashRPP/SecurityToken');
        $securityToken = (string) $nodes[0];

        $error = $xml->xpath('/UKashRPP/errCode');
        self::error($error[0]);

        //Return the UTID value to the calling function.
        return array('utid' => $UTID, 'securityToken' => $securityToken);
    }

    public static function checkTransaction($securityToken, $brandID, $uTID, $actionUrl) {
        //build up an array of the data to post to get TransactionID

        $DataToPost = array('SecurityToken' => '' . $securityToken . '',
                                'BrandID' => '' . $brandID . '',
                                'UTID' => '' . $uTID . '' );


        $XmlResult = (self::DoHttpPost($actionUrl, $DataToPost));
        $xml = new SimpleXmlElement($XmlResult);

        //Decode the xml strings value
        $decodedstring = self::url_decode($xml);
        
        $xml = new SimpleXmlElement($decodedstring);

        $retArr = array();
        $nodes = $xml->xpath('/UKashRPP/SecurityToken');
        $retArr['securityToken'] = (string) $nodes[0];
        
        $nodes = $xml->xpath('/UKashRPP/UTID');
        $retArr['uTID'] = (string) $nodes[0];
        
        $nodes = $xml->xpath('/UKashRPP/TransactionCode');
        $retArr['transactionCode'] = (string) $nodes[0];
        
        $nodes = $xml->xpath('/UKashRPP/TransactionDesc');
        $retArr['transactionDesc'] = (string) $nodes[0];
        
        $nodes = $xml->xpath('/UKashRPP/MerchantTransactionID');
        $retArr['order_id'] = (string) $nodes[0];
        
        $nodes = $xml->xpath('/UKashRPP/SettleAmount');
        $retArr['settleAmount'] = (string) $nodes[0];
        
        $nodes = $xml->xpath('/UKashRPP/MerchantCurrency');
        $retArr['merchantCurrency'] = (string) $nodes[0];
        
        $nodes = $xml->xpath('/UKashRPP/UkashTransactionID');
        $retArr['ukashTransactionID'] = (string) $nodes[0];

        $nodes = $xml->xpath('/UKashRPP/errCode');
        $retArr['errorid'] =(string) $nodes[0];

        $nodes = $xml->xpath('/UKashRPP/errDescription');
        $retArr['errDescription'] =(string) $nodes[0];
        
        return $retArr;
    }

    /**
     * DEBUG Bei Fehler sende mir eine Mail
     * @param type $errorcode
     */
    private static function error($errorcodeID) {

        $errorcodes = array(0 => "Accepted Redemption successful",
            1 => "Declined Redemption unsuccessful",
            99 => "Failed	An error occurred during the processing of the transaction hence the system could not successfully complete the redemption of the voucher. Will also be returned if an invalid voucher number was supplied.",
            100 => "Invalid incoming XML / Invalid Value for UserEmailAddress /	Invalid Value for UserCountry / Invalid Value for UserIP / Invalid Value for UserUniqueID",
            200 => "Non numeric Voucher Value",
            201 => "Base Currency not 3 characters in length",
            202 => "Non numeric Ticket Value",
            203 => "Invalid BrandId",
            204 => "Invalid MerchDateTime",
            205 => "Invalid transactionId: greater than 20 characters",
            206 => "Invalid Redemption Type",
            207 => "Negative Ticket Value not allowed",
            208 => "No decimal place given in Ticket Value",
            209 => "No decimal place given in Voucher Value",
            210 => "Negative Voucher Value not allowed",
            211 => "Invalid or unsupported voucher product code",
            212 => "AmountReference with TicketValue not allowed",
            213 => "No ukashNumber supplied",
            214 => "No transactionId supplied",
            215 => "No brandId supplied",
            216 => "Ticket Value cannot be greater than Voucher Value without Currency Conversion",
            217 => "Base Currency and Voucher currency do not match.",
            218 => "Brand not configured to Issue Vouchers",
            219 => "Invalid Voucher Number",
            221 => "Multiple Transactions found",
            222 => "Unknown transaction status",
            223 => "No transaction found.",
            300 => "Invalid Login and/or Password",
            301 => "Invalid Login and/or BrandID",
            400 => "Required Currency Conversion not supported",
            500 => "Error In Currency Conversion",
            501 => "Converted Settle Amount greater than Voucher Value",
            900 => "Technical Error. Please contact Ukash Merchant Support.");

    }

}

?>
