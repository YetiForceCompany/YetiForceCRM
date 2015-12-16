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
require_once("languages/" . $current_language . "/OSSPdf.php");

include_once('config/config.php');
include_once('include/database/PearDatabase.php');
include_once('vtlib/Vtiger/Utils.php');
include_once('include/utils/utils.php');
include_once('ShowModuleIdField.php');

vimport('include/runtime/Viewer.php');

function Popup($request) {
    $db = PearDatabase::getInstance();
	$site_URL = vglobal('site_URL');
    $templates_dir = "modules/OSSPdf/templates";
    
    if ($request->get('filename') != NULL && $request->get('filename') != '') {
        $filename = $templates_dir . "/" . $_REQUEST['filename'];
        $handle = fopen($filename, "rb");
        print fread($handle, filesize($filename));
        fclose($handle);
    } else {

        if (( $request->get('selected_module') != NULL && $request->get('selected_module') != '' ) && $request->get('selected_module') != 0 && $request->get('selected_module') != '') {
            $moduleid = $request->get('selected_module');
        } elseif ($request->get('moduleid') == NULL || $request->get('moduleid') == 0) {
            $wynik = $db->query("select tabid,name from vtiger_tab where isentitytype='1' and presence<> '2'", true);
            $moduleid = $db->query_result($wynik, 0, "tabid");
        } else {
            $moduleid = $request->get('moduleid');
        }
        
        $chosenid = $moduleid;
        $productmodule = 'yes';
        $pobierz = $db->query("select name from vtiger_tab where tabid = '$moduleid'", true);
        $modulename = $db->query_result($pobierz, 0, "name");

        $chosen_module = $modulename;

        $pobierz_bloki = $db->query("select blockid, blocklabel from vtiger_blocks where tabid = '$moduleid'", true);
        $field_list = Array();
        for ($k = 0; $k < $db->num_rows($pobierz_bloki); $k++) {
            $blockid = $db->query_result($pobierz_bloki, $k, "blockid");
            $label = $db->query_result($pobierz_bloki, $k, "blocklabel");
            $pobierz_pola = $db->query("select fieldname,fieldlabel from vtiger_field where block='$blockid' and tabid = '$moduleid'", true);

            for ($i = 0; $i < $db->num_rows($pobierz_pola); $i++) {
                $field_list[vtranslate($label, $modulename)][$i]['name'] = $db->query_result($pobierz_pola, $i, "fieldname");
                $field_list[vtranslate($label, $modulename)][$i]['label'] = vtranslate($db->query_result($pobierz_pola, $i, "fieldlabel"), $modulename);
            }
        }
        $uitypelist = Array('10', '58', '51', '57', '68', '59', '75', '80', '76', '73', '81', '53', '52', '78');
        $uitype2module = Array('58' => 'Campaigns',
            '51' => 'Accounts',
            '57' => 'Contacts',
            '68' => 'Accounts;Contacts',
            '59' => 'Products',
            '75' => 'Vendors',
            '76' => 'Potentials',
            '73' => 'Accounts',
            '81' => 'Vendors',
            '53' => 'Users',
            '52' => 'Users');
        $pobierz = $db->query("select fieldid,uitype from vtiger_field where tabid = '$moduleid'", true);
        for ($i = 0; $i < $db->num_rows($pobierz); $i++) {
            $uitype = $db->query_result($pobierz, $i, "uitype");
            $fieldid = $db->query_result($pobierz, $i, "fieldid");
            if (in_array($uitype, $uitypelist)) {
                if ($uitype == '10') {
                    $wynik = $db->query("select relmodule from vtiger_fieldmodulerel where fieldid = '$fieldid'", true);
                    for ($k = 0; $k < $db->num_rows($wynik); $k++) {
                        $list[$db->query_result($wynik, $k, "relmodule")] = vtranslate($db->query_result($wynik, $k, "relmodule"), $db->query_result($wynik, $k, "relmodule"));
                    }
                } else {
                    $zmienna = $uitype2module[$uitype];
                    $zmienna = explode(';', $zmienna);
                    foreach ($zmienna as $value) {
                        $list[$value] = vtranslate($value, $value);
                    }
                }
            }
        }
        $modulename = '';
        if ($request->get('relatedmoduleid') != NULL) {
            $modulename = $request->get('relatedmoduleid');
        } else {
            if (count($list) > 0) {
                foreach ($list as $name => $record) {
                    $modulename = $name;
                    break;
                }
            }
        }
        //echo $modulename;
        if ($modulename != '') {
            $pobierz = $db->query("select tabid from vtiger_tab where name = '$modulename'", true);
            $moduleid = $db->query_result($pobierz, 0, "tabid");

            $pobierz_bloki = $db->query("select blockid, blocklabel from vtiger_blocks where tabid = '$moduleid'", true);
            $relatedfield_list = Array();
            for ($k = 0; $k < $db->num_rows($pobierz_bloki); $k++) {
                $blockid = $db->query_result($pobierz_bloki, $k, "blockid");
                $label = $db->query_result($pobierz_bloki, $k, "blocklabel");
                $pobierz_pola = $db->query("select fieldname,fieldlabel from vtiger_field where block='$blockid' and tabid = '$moduleid'", true);

                for ($i = 0; $i < $db->num_rows($pobierz_pola); $i++) {
                    if ($modulename == 'Users' && ( $db->query_result($pobierz_pola, $i, "fieldname") == 'accesskey' || $db->query_result($pobierz_pola, $i, "fieldname") == 'user_password' || $db->query_result($pobierz_pola, $i, "fieldname") == 'confirm_password' )) {
                        
                    } else {
                        $relatedfield_list[vtranslate($label, $modulename)][$i]['name'] = $db->query_result($pobierz_pola, $i, "fieldname");
                        $relatedfield_list[vtranslate($label, $modulename)][$i]['label'] = vtranslate($db->query_result($pobierz_pola, $i, "fieldlabel"), $modulename);
                    }
                }
            }
        }
        if (count($list) == 0) {
            $list[0] = vtranslate('LBL_empty', 'OSSPdf');
        }
        $templates = Array();
        if (is_dir($templates_dir)) {
            if ($handle = opendir($templates_dir)) {
                while ($file = readdir($handle)) {
                    if (strstr($file, ".html")) {
                        $templates[] = $file;
                    }
                }
                closedir($handle);
            }
        }
        
        $pobierz = $db->query("select logoname from vtiger_organizationdetails", true);
        $logo = $db->query_result($pobierz, 0, "logoname");
        $company = array(
            'company_organizationname' => vtranslate('LBL_ORGANIZATION_NAME', 'OSSPdf'),
            'storage/Logo/' . $logo => vtranslate('LBL_ORGANIZATION_LOGO', 'OSSPdf'),
            'company_address' => vtranslate('LBL_ORGANIZATION_ADDRESS', 'OSSPdf'),
            'company_city' => vtranslate('LBL_ORGANIZATION_CITY', 'OSSPdf'),
            'company_state' => vtranslate('LBL_ORGANIZATION_STATE', 'OSSPdf'),
            'company_code' => vtranslate('LBL_ORGANIZATION_CODE', 'OSSPdf'),
            'company_country' => vtranslate('LBL_ORGANIZATION_COUNTRY', 'OSSPdf'),
            'company_phone' => vtranslate('LBL_ORGANIZATION_PHONE', 'OSSPdf'),
            'company_fax' => vtranslate('LBL_ORGANIZATION_FAX', 'OSSPdf'),
			'company_vatid' => vtranslate('LBL_ORGANIZATION_VAT', 'OSSPdf'),
            'company_website' => vtranslate('LBL_ORGANIZATION_WEBSITE', 'OSSPdf')
        );
    
        $modtab = array(20, 21, 22, 23);

        // lista funkcji specjalnych które mają pojawić się tylko w nietórych modułach
        $funtab = array('amount_in_words', 'replaceProductList', 'replaceProductTable', 'replaceProductTableNP');

        $PRODMODULE = Array();

        $dir = dir("modules/OSSPdf/special_functions");
        while ($file = $dir->read()) {
            if ($file != '.' && $file != '..' && $file != 'example.php') {
                include( "modules/OSSPdf/special_functions/" . $file );
                $functionname = str_replace(".php", "", $file);
                if (in_array('all', $permitted_modules)) {
                    $PRODMODULE["#special_function#$functionname#end_special_function#"] = vtranslate($functionname, "OSSPdf");
                } else {

                    if (in_array(getTabModuleName($chosenid), $permitted_modules)) {
                        $PRODMODULE["#special_function#$functionname#end_special_function#"] = vtranslate($functionname, "OSSPdf");
                    }
                }
            }
        }

        $pobierz = "select vtiger_reportmodules.reportmodulesid as id, vtiger_report.reportname as name from vtiger_reportmodules
        INNER JOIN vtiger_report on (vtiger_report.reportid = vtiger_reportmodules.reportmodulesid ) where vtiger_reportmodules.primarymodule like '%$chosen_module'	OR vtiger_reportmodules.secondarymodules like '%$chosen_module%'";

        $zapytanie = $db->query($pobierz, true);
        $reports = array();
        for ($i = 0; $i < $db->num_rows($zapytanie); $i++) {
            $reports[$db->query_result($zapytanie, $i, "id")] = vtranslate($db->query_result($zapytanie, $i, "name"), "Reports");
        }

        $viewer = new Vtiger_Viewer();
        $viewer->assign("MODULE", $modulename);
        $viewer->assign("TABLIST", ShowModuleIdField($request->get('selected_module'), true));
        $viewer->assign("SELECTED_MODULE", $request->get('selected_module'));
        $viewer->assign("PRODMODULE", $PRODMODULE);
        $viewer->assign('ProductModule', $productmodule);
        $viewer->assign("ChosenModule", $request->get('selected_module'));
        $viewer->assign("RELMODULE", $list);
        $viewer->assign("DEFAULT_FIELDS", $field_list);
        $viewer->assign("RELATEDFIELDS", $relatedfield_list);
        $viewer->assign("TEMPLATES", $templates);
        $viewer->assign("COMPANY", $company);
        $viewer->assign("REPORTS", $reports);
        $viewer->assign("LBL_Label", vtranslate("LBL_Label", 'OSSPdf'));
        $viewer->assign("LBL_Field", vtranslate("LBL_Field", 'OSSPdf'));
        $viewer->assign("LBL_CHOSENMODULE", vtranslate("LBL_CHOSENMODULE", 'OSSPdf'));
        $viewer->assign("LBL_INSERTREPORT", vtranslate("LBL_INSERTREPORT", 'OSSPdf'));
        $viewer->assign("LBL_COMPANY_DETAILS", vtranslate("LBL_COMPANY_DETAILS", "Settings"));
        $viewer->assign("LBL_PRODUCT_MODULE", vtranslate("LBL_PRODUCT_MODULE", 'OSSPdf'));
        $viewer->assign("LBL_DEFAULT_FIELDS", vtranslate("LBL_DEFAULT_FIELDS", 'OSSPdf'));
        $viewer->assign("LBL_RELATED_MODULE", vtranslate("LBL_RELATED_MODULE", 'OSSPdf'));
        $viewer->assign("LBL_RELATED_FIELDS", vtranslate("LBL_RELATED_FIELDS", 'OSSPdf'));
        $viewer->assign("LBL_GET_VARIABLE", vtranslate("LBL_GET_VARIABLE", 'OSSPdf'));
        $viewer->assign("LBL_SET_DEFAULT_TEMPLATE", vtranslate("LBL_SET_DEFAULT_TEMPLATE", 'OSSPdf'));
        $viewer->assign("LBL_SELECT_TEMPLATE", vtranslate("LBL_SELECT_TEMPLATE", 'OSSPdf'));
        $viewer->assign("LBL_SELECT_FIELD", vtranslate("LBL_SELECT_FIELD", 'OSSPdf'));
        if ($request->get('changedindex') == 'true') {
            return $viewer->fetch('modules/OSSPdf/selectlist.tpl');
        } elseif ($request->get('changerelatedmodule') == 'true') {
            return $viewer->fetch('modules/OSSPdf/newvalues.tpl');
        } else {
            return $viewer->fetch('modules/OSSPdf/popup.tpl');
        }
    }
}
