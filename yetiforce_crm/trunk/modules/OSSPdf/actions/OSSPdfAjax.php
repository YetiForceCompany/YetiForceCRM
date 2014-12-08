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
include_once('config/config.php');
include_once('vtlib/Vtiger/Utils.php');
include_once('include/utils/utils.php');

include_once('file/ShowModuleIdField.php');
include_once('file/Popup.php');

class OSSPdf_OSSPdfAjax_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        $db = PearDatabase::getInstance();
        
        if($request->get('file') == 'ShowModuleIdField'){

            $html = ShowModuleIdField($request->get('selected_module'));
            
            $response = new Vtiger_Response();
            $response->setResult($html);
            $response->emit();
        }
        elseif( $request->get('file') == 'CheckForTemplates' )
        {
            include( "modules/OSSPdf/CheckForTemplates_function.php" );
            $value = check();
            $response = new Vtiger_Response();
            $response->setResult( $value );
            $response->emit();
        }
        elseif( $request->get('file') == 'PDFExport' )
        {
            include( "modules/OSSPdf/PDFExport.php" );
        }
        else{
            if ($request->get('mode') == 'Popup'){
                $html = Popup($request);
                
                $response = new Vtiger_Response();
                $response->setResult($html);
                $response->emit();
            }
        }
    }
}
?>
