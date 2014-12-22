<?php

class OSSMenuManager_MenuCreation_View extends Vtiger_Edit_View {
	public function checkPermission(Vtiger_Request $request) {

	}
	public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
		$modules = $adb->query( "SELECT tabid,name FROM vtiger_tab WHERE name NOT iN('Dashboard','Users','Webforms','OSSMenuManager','CustomerPortal','ModTracker','WSAPP','Google','Import','Mobile','ModComments','Rss','Events') ORDER BY name", true );
		$ossmenumanager = $adb->query( "SELECT * FROM vtiger_ossmenumanager WHERE parent_id = '0' ORDER BY sequence", true );

		// tlumaczenie blokow
		$blocksMenu = $ossmenumanager->GetArray();
		for($i=0;$i<count($blocksMenu);$i++){
			if(!empty($blocksMenu[$i]['langfield'])){
				$res = explode('#',$blocksMenu[$i]['langfield']);
				for ($j=0; count($res)>$j; $j++){
					$prefix=substr( $res[$j], 0, strpos($res[$j], "*") );
					$value=substr( $res[$j], 6 );
					if ($prefix==Users_Record_Model::getCurrentUserModel()->get('language')){
						$blocksMenu[$i]['label'] = $value;
						break;
					}
				}
			}
		}
		
	
		$block = $request->get('block');
		$recordModel = Vtiger_Record_Model::getCleanInstance( 'OSSMenuManager' );  
        $menuRecord = $recordModel->getMenuRecord( $block );

        // profile
         $sql = "SELECT `profilename`,`profileid` FROM `vtiger_profile`;";
        $result = $adb->query( $sql, true );
        $num = $adb->num_rows( $result );
        
        $profiles = array();
        for ( $i=0; $i<$num; $i++ ) {
            $profiles[$adb->query_result( $result, $i, 'profileid' )] = $adb->query_result( $result, $i, 'profilename' );
        }
		$menuRecord['acces'] = explode(' |##| ',$menuRecord['permission']);
		
	//	echo '<pre>';print_r($modules->GetArray());echo '</pre>';exit;
        $viewer = $this->getViewer($request);
		$viewer->assign( "TYPES", $recordModel->types_menu);
		$viewer->assign( "MODULES", $modules->GetArray());
		$viewer->assign( "BLOCK", $blocksMenu);
		$viewer->assign( "PROFILES", $profiles);
		$viewer->assign( "MENU_RECORD", $menuRecord);
        $viewer->view('MenuCreation.tpl', 'OSSMenuManager');
	}
}
?>
