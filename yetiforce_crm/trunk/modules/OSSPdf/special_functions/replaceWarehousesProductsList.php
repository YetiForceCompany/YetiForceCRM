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
$permitted_modules = array('OSSAcceptanceOfDelivery', 'OSSDocumentDeficiencies', 'OSSDocumentSurples', 'OSSGoodsIssue', 'OSSGoodsIssueCorrection', 'OSSOrderGoodsIssue', 'OSSProductsDiscrepancies', 'OSSProductsTransfer', 'OSSStockReservation', 'OSSStockTransferAdd', 'OSSStockTransferRemove', 'OSSDocumentsLKOM');

/// ZNACZNIK WYWOLUJACY TE FUNKCJE => #special_function#example#end_special_function#
/// funkcja MUSI miec taką samą nazwe jak PLIK
function replaceWarehousesProductsList($pdftype, $id, $templateid,$content, $tcpdf) {
    
    $current_language = Users_Record_Model::getCurrentUserModel()->get('language');
    
    include("languages/" . $current_language . "/OSSPdf.php");
    require_once( 'include/utils/CommonUtils.php' );
    require_once( 'include/fields/CurrencyField.php' );
    require_once( 'vtiger6/modules/OSSWarehouses/views/Utils.php' );
    require_once( 'modules/' . $pdftype . '/' . $pdftype . '.php' );
    require_once('Smarty_setup.php');

    $smarty = new Vtiger_Viewer();
    GetProducts($pdftype, $id, $smarty);
    $smarty->assign('MODULE', $pdftype);
    return $smarty->fetch('modules/OSSWarehouses/ProductTablePDF.tpl');
}

?>