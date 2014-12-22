<?php

class OSSMenuManager_MenuEdition_View extends Vtiger_Edit_View {
	public function checkPermission(Vtiger_Request $request) {

	}
	public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
		$modules = $adb->query( "SELECT tabid,name FROM vtiger_tab WHERE name NOT iN('Dashboard','Users','Webforms','OSSMenuManager','CustomerPortal','ModTracker','WSAPP','Google','Import','Mobile','ModComments','Rss','Events') ORDER BY name", true );
		$ossmenumanager = $adb->query( "SELECT * FROM vtiger_ossmenumanager WHERE parent_id = '0' ORDER BY sequence", true );
 
 
		        // profile
        $sql = "SELECT `profilename`,`profileid` FROM `vtiger_profile`;";
        $result = $adb->query( $sql, true );
        $num = $adb->num_rows( $result );
        
        $profiles = array();
        for ( $i=0; $i<$num; $i++ ) {
            $profiles[$adb->query_result( $result, $i, 'profileid' )] = $adb->query_result( $result, $i, 'profilename' );
        }
		
		$recordModel = Vtiger_Record_Model::getCleanInstance( 'OSSMenuManager' );
		
        $menuTypes = $recordModel->types_menu;
		$id = $request->get( 'id' );
        $menuRecord = $recordModel->getMenuRecord( $id );
	
        $menuRecord['type'] = $menuRecord[6] = $menuTypes[$menuRecord['type']];
		$menuRecord['acces'] = explode(' |##| ',$menuRecord['permission']);
		
		
		
    //	echo '<pre>';print_r($menuRecord);exit;    
        $viewer = $this->getViewer($request);
		$viewer->assign( "TYPES", $recordModel->types_menu);
		$viewer->assign( "PROFILES", $profiles);
		$viewer->assign( "MODULES", $modules->GetArray());
		$viewer->assign( "BLOCK", $ossmenumanager->GetArray());
		//$viewer->assign( "ICON", $ossmenumanagericon->GetArray());
        $viewer->assign( "MENU_RECORD", $menuRecord);
        $viewer->view('MenuEdition.tpl', 'OSSMenuManager');
		
	}
}
?>
