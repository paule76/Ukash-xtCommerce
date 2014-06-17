<?php

/**
 * -----------------------------------------------------------------------------------------
 * $Id: ukash.php  2013-07-17
 * @author Stefan Paul 
 * Released under the GNU General Public License
 * --------------------------------------------------------------------------------------- */


define('MODULE_PAYMENT_UKASH_TEXT_TITLE', 'Ukash');
//define('MODULE_PAYMENT_UKASH_TEXT_DESCRIPTION', 'TEXT_DESCRIPTION');
define('MODULE_PAYMENT_UKASH_TEXT_DESCRIPTION', 'Gib hier Deinen 19-stelligen Ukash-Code ein, den Du an einem nahegelegenen <a href="https://www.ukash.com/de-DE/where-to-get/">Ukash-Standort</a> erwerben kannst. Mit <strong>Ukash</strong> bezahlst Du &auml;hnlich wie mit einer Prepaid-Karte &ndash; bist aber noch flexibler!'); 


define('MODULE_PAYMENT_UKASH_SORT_ORDER_TITLE', 'MODULE_PAYMENT_UKASH_SORT_ORDER_TITLE');
define('MODULE_PAYMENT_UKASH_TMP_STATUS_ID_TITLE', 'MODULE_PAYMENT_UKASH_TMP_STATUS_ID_TITLE');
define('MODULE_PAYMENT_UKASH_TMP_STATUS_ID_DESC', 'MODULE_PAYMENT_UKASH_TMP_STATUS_ID_DESC');
define('MODULE_PAYMENT_UKASH_CALLBACK_URL_DESC', 'MODULE_PAYMENT_UKASH_CALLBACK_URL_DESC');
//define('', '');


define('MODULE_PAYMENT_UKASH_TEXT_EMAIL_FOOTER', str_replace('<br />', '\n', MODULE_PAYMENT_UKASH_TEXT_DESCRIPTION));

define('MODULE_PAYMENT_UKASH_TEXT_INFO', 'Gib hier Deinen 19-stelligen Ukash-Code ein, den Du an einem nahegelegenen <a href="https://www.ukash.com/de-DE/where-to-get/">Ukash-Standort</a> erwerben kannst. Mit <strong>Ukash</strong> bezahlst Du &auml;hnlich wie mit einer Prepaid-Karte &ndash; bist aber noch flexibler!'); 

define('MODULE_PAYMENT_UKASH_STATUS_TITLE', 'Allow Ukash Payment');
define('MODULE_PAYMENT_UKASH_STATUS_DESC', 'Do you want to accept Ukash order payments?');

define('MODULE_PAYMENT_UKASH_SECURITY_TOKEN_TITLE', 'Security Token');
define('MODULE_PAYMENT_UKASH_SECURITY_TOKEN_DESC', 'Default UKASH Testtoken! Für Produktiv tauschen');

define('MODULE_PAYMENT_UKASH_BRANDID_TITLE', 'BrandIDs');
define('MODULE_PAYMENT_UKASH_BRANDID_DESC', 'Ukash Brand Id example de=UKASH1212,fr=UKASH1233,es=UKASH11234,....');

define('MODULE_PAYMENT_UKASH_BRANDID_DEFAULT_COUNTRY_TITLE', 'Default Country');
define('MODULE_PAYMENT_UKASH_BRANDID_DEFAULT_COUNTRY_DESC', 'Fallback Country');

define('MODULE_PAYMENT_UKASH_CURRENCY_TITLE', 'Default W&auml;hrung');
define('MODULE_PAYMENT_UKASH_CURRENCY_DESC', 'Im Moment mal nur Euro');

define('MODULE_PAYMENT_UKASH_URL_SUCCESS_TITLE', 'Success Callback Url:');
define('MODULE_PAYMENT_UKASH_URL_SUCCESS_DESC', '');

define('MODULE_PAYMENT_UKASH_URL_FAIL_TITLE', 'Fail Callback Url');
define('MODULE_PAYMENT_UKASH_URL_FAIL_DESC', '');

define('MODULE_PAYMENT_UKASH_URL_NOTIFICATON_TITLE', 'Notification Url');
define('MODULE_PAYMENT_UKASH_URL_NOTIFICATON_DESC', 'Muss ich noch klären was die machen...');

define('MODULE_PAYMENT_UKASH_CALLBACK_URL_TITLE', 'Module Sort order of display.');
define('MODULE_PAYMENT_UKASH_SORT_ORDER_DESC', 'Sort order of display. Lowest is displayed first.');

define('MODULE_PAYMENT_UKASH_CALLBACK_URL_TITLE', 'callback url ');
define('MODULE_PAYMENT_UKASH_SORT_ORDER_DESC', 'z.B. http://server.domain/callback/ukash/callback.php');

define('MODULE_PAYMENT_UKASH_URL_REDIRECT_TITLE', 'Redirect Target.');
define('MODULE_PAYMENT_UKASH_URL_REDIRECT_DESC', '');

define('MODULE_PAYMENT_UKASH_URL_ACTION_TITLE', 'Action Target.');
define('MODULE_PAYMENT_UKASH_URL_ACTION_DESC', '');

define('MODULE_PAYMENT_UKASH_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_UKASH_ALLOWED_DESC', 'Gib bitte <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
?>
