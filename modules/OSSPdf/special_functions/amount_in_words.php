<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
$current_language = Users_Record_Model::getCurrentUserModel()->get('language');
if(!file_exists("languages/" . $current_language . "/OSSPdf.php"))
	$current_language = "en_us";
require_once("include/fields/CurrencyField.php");
require_once("languages/" . $current_language . "/OSSPdf.php");
require_once 'modules/OSSPdf/OSSPdf.php';

$permitted_modules = array('Quotes', 'Invoice', 'SalesOrder', 'PurchaseOrder', 'OSSWarehouseReleases', 'OSSWarehouseAdoptions', 'OSSWarehouseReservations');

function amount_in_words($module, $id, $templateid, $content, $tcpdf) {
    $db = PearDatabase::getInstance();
    $current_language = Users_Record_Model::getCurrentUserModel()->get('language');
	if(!file_exists("languages/" . $current_language . "/OSSPdf.php"))
		$current_language = "en_us";
    $mod = $module;
    $module = strtolower($module);

    if ($module == 'quotes') {
        $idcol = "quoteid";
    } else {
        $idcol = $module . "id";
    }

    $sql = "SELECT total FROM vtiger_$module WHERE $idcol = " . $id;
    $result = $db->query($sql, true);
    $grand_total = $db->query_result($result, 0, 'total');
    $currfield = new CurrencyField($grand_total);

    $grand_total = $currfield->getDBInsertedValue($grand_total);

    require_once( 'include/utils/utils.php' );
    require_once( 'include/utils/CommonUtils.php' );

    require_once( 'modules/' . $mod . '/' . $mod . '.php' );
    $focus = new $mod();

    $focus->retrieve_entity_info($id, $mod);
    $currency_id = $focus->column_fields['currency_id'];
    $pobierz = $db->query("select currency_symbol, currency_code from vtiger_currency_info where id = '$currency_id'", true);
    $kod_aktualnej_waluty = $db->query_result($pobierz, 0, "currency_code");


    $recordModel = Vtiger_Record_Model::getCleanInstance( 'OSSPdf' );
    $kwota = $recordModel->slownie($grand_total, $kod_aktualnej_waluty);
    return $kwota;
}

?>