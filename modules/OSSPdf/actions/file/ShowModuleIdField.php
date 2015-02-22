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
include_once('include/database/PearDatabase.php');
include_once('vtlib/Vtiger/Utils.php');
include_once('include/utils/utils.php');

vimport ('include/runtime/Viewer.php');

function ShowModuleIdField($selected_module, $tab_only = false) {
    
    $db = PearDatabase::getInstance();
    
    $names = array('Emails','PBXManager','ModComments','SMSNotifier','OSSPdf');
            
    foreach( $names as $id )
    {
        $in .= "'" . $id . "',";
    }

    $in = trim($in,',');

    $query = "select tabid, name, customized from vtiger_tab where isentitytype = '1' and presence <> '2' and name not in ( $in )";
    $wynik = $db->query( $query, true );

    $tablist = array();

    for( $i = 0; $i < $db->num_rows($wynik); $i++ )
    {
        $tablist[$i]['id'] = $db->query_result( $wynik, $i, "tabid" );

        $label = $db->query_result( $wynik, $i,"name" );

        if( $db->query_result( $wynik, $i, "customized" )  == 0 )
        {
                $tablist[$i]['label'] =  getTranslatedString($label);
        }
        else
        {
                $tablist[$i]['label'] = getTranslatedString($label,$label);
        }
    }
	if($selected_module == ''){
		$SMODULE = $tablist[0]['id'];
	}else{
		$SMODULE = $selected_module;
	}
	if($tab_only == false){
		$viewer = new Vtiger_Viewer();
		$viewer->assign( "TABLIST", $tablist );
		$viewer->assign( "SMODULE", $SMODULE );	
		$viewer->assign( "SELECTED_MODULE", $selected_module);
		
		return $viewer->fetch('modules/OSSPdf/FieldModuleid.tpl');
	}
	else{
		return $tablist;
	}
}

?>
