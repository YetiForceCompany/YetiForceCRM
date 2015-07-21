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
vimport('~include/database/PearDatabase.php');
vimport('~include/utils/utils.php');
vimport('~include/utils/UserInfoUtil.php');
vimport('~modules/Vtiger/layout_utils.php');

class OSSPdf_ListViewExportPDFRecords_View extends Vtiger_Index_View {

    function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        
        $list_max_entries_per_page = vglobal('list_max_entries_per_page');
        $theme = vglobal('theme');
        $db = PearDatabase::getInstance();

        $smarty = new Vtiger_Viewer();

        //include_once( 'modules/OSSPdf/constraints_methods.php' );

        $theme_path = "themes/" . $theme . "/";
        $image_path = $theme_path . "images/";
        $idstring = $request->get('record');
        $module_name = $request->get('usingmodule');
        $pobierz_id = $db->query("select tabid from vtiger_tab where name = '$module_name'", true);
        $tabid = $db->query_result($pobierz_id, 0, "tabid");

        $pobierz_rekordy = $db->query("select * from vtiger_osspdf inner join vtiger_crmentity on (vtiger_crmentity.crmid = vtiger_osspdf.osspdfid ) where moduleid = '$tabid' and deleted <> '1'", true);

        $permitted_templates = array();
        $indeks = 0;
        for ($i = 0; $i < $db->num_rows($pobierz_rekordy); $i++) {
            $rekord = $db->query_result($pobierz_rekordy, $i, "osspdfid");
            $name = $db->query_result($pobierz_rekordy, $i, "title");
			$selected = $db->query_result($pobierz_rekordy, $i, "selected");
			$osspdf_view = $db->query_result($pobierz_rekordy, $i, "osspdf_view");
            if ($request->get('fromdetailview') == '') {
				//echo '<pre>';var_dump($rekord, $idstrin);echo '</pre>';
                if (isPermitted("OSSPdf", "DetailView", $rekord) == 'yes') {
					if ( !(strpos($osspdf_view, 'List') === false) || !(strpos($osspdf_view, vtranslate( 'List', 'OSSPdf')) === false) )  {
                        $permitted_templates[$indeks]['id'] = $rekord;
                        $permitted_templates[$indeks]['name'] = $name;
                        $permitted_templates[$indeks]['checked'] = $selected;
                        $indeks++;  
					}
                }
            }
        }
        if ($indeks == 0) {
            $smarty->assign("NO_TEMPLATES", "yes");
        } else {
            $smarty->assign("NO_TEMPLATES", "no");
        }

        if ($request->get('fromdetailview') != '') {
            $smarty->assign("FROM_DETAILVIEW", "yes");
        } else {
            $smarty->assign("FROM_DETAILVIEW", "no");
        }

        $TABLE = array('4A0' => '4A0',
            '2A0' => '2A0',
            'A0' => 'A0',
            'A1' => 'A1',
            'A2' => 'A2',
            'A3' => 'A3',
            'A4' => 'A4',
            'A5' => 'A5',
            'A6' => 'A6',
            'A7' => 'A7',
            'A8' => 'A8',
            'A9' => 'A9',
            'A10' => 'A10',
            'B0' => 'B0',
            'B1' => 'B1',
            'B2' => 'B2',
            'B3' => 'B3',
            'B4' => 'B4',
            'B5' => 'B5',
            'B6' => 'B6',
            'B7' => 'B7',
            'B8' => 'B8',
            'B9' => 'B9',
            'B10' => 'B10',
            'C0' => 'C0',
            'C1' => 'C1',
            'C2' => 'C2',
            'C3' => 'C3',
            'C4' => 'C4',
            'C5' => 'C5',
            'C6' => 'C6',
            'C7' => 'C7',
            'C8' => 'C8',
            'C9' => 'C9',
            'C10' => 'C10',
            'RA0' => 'RA0',
            'RA1' => 'RA1',
            'RA2' => 'RA2',
            'RA3' => 'RA3',
            'RA4' => 'RA4',
            'SRA0' => 'SRA0',
            'SRA1' => 'SRA1',
            'SRA2' => 'SRA2',
            'SRA3' => 'SRA3',
            'SRA4' => 'SRA4',
            'LETTER' => 'LETTER',
            'LEGAL' => 'LEGAL',
            'EXECUTIVE' => 'EXECUTIVE',
            'FOLIO' => 'FOLIO');
        if ($request->get('usingmodule') == 'Reports') {
            $smarty->assign('advft_criteria', htmlspecialchars($request->get('advft_criteria')));
            $smarty->assign('advft_criteria_groups', htmlspecialchars($request->get('advft_criteria_groups')));
        }
        $smarty->assign('Formats', $TABLE);
        $smarty->assign("SESSION_WHERE", $_SESSION['export_where']);
        $smarty->assign("templates", $permitted_templates);
        $smarty->assign('APP', $app_strings);
        $smarty->assign('MOD', $mod_strings);
        $smarty->assign("THEME", $theme_path);
        $smarty->assign("IMAGE_PATH", $image_path);
        $smarty->assign("RECORD", $idstring);
        $smarty->assign("MODULE", $module_name);
        $smarty->assign("USINGMODULE", $module_name);
        $smarty->assign("MODULELABEL", getTranslatedString($module_name));
        $smarty->assign("IDSTRING", $idstring);
        $smarty->assign("OSS_MILE_EXISTS", OSSPdf_Module_Model::moduleIsActive('OSSMail'));
        $smarty->assign("PERPAGE", $list_max_entries_per_page);
        $smarty->view('ListViewExportRecords.tpl', 'OSSPdf');
    }

}

?>
