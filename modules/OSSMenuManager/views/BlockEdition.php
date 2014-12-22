<?php
class OSSMenuManager_BlockEdition_View extends Vtiger_Edit_View {
	public function checkPermission(Vtiger_Request $request) {

	}
	public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $recordModel = Vtiger_Record_Model::getCleanInstance( 'OSSMenuManager' ); 
        
        $id = intval($request->get( 'id' ));
        
		require_once ('include/utils/VtlibUtils.php');
        // profile
        $sql = "SELECT `profilename`,`profileid` FROM `vtiger_profile`;";
        $result = $adb->query( $sql, true );
        $num = $adb->num_rows( $result );
        
        $profiles = array();
        for ( $i=0; $i<$num; $i++ ) {
            $profiles[$adb->query_result( $result, $i, 'profileid' )] = $adb->query_result( $result, $i, 'profilename' );
        }
		
        
		
		$langp=Users_Record_Model::getCurrentUserModel()->get('language');
		$langfield = $langfield=vtlib_getToggleLanguageInfo();
	
        // ilość pozycji menu w  bloku
        $sql = "SELECT COUNT(1) as nr FROM `vtiger_ossmenumanager` WHERE `parent_id` = ?;";
        $params = array( intval($id) );
        $result = $adb->pquery( $sql, $params, true );
        $num = $adb->query_result( $result, 0, 'nr' );
        
        $blockRecord = $recordModel->getMenuRecord( $id );
		$blockRecord['acces'] = explode(' |##| ',$blockRecord['permission']);
//	echo '<pre>';print_r($blockRecord);exit;	

				$res = explode('#',$blockRecord[langfield]);
				for ($i=0; count($res)>$i; $i++){
					$prefix=substr( $res[$i], 0, strpos($res[$i], "*") );
					$value[$prefix]=substr( $res[$i], 6 );
					}
	
		
	//	echo '<pre>';print_r($value);exit;	
		
	
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULENAME', 'OSSMenuManager');
		$viewer->assign('PROFILES', $profiles);
		$viewer->assign('BLOCK_RECORD', $blockRecord);
        $viewer->assign('BLOCKID', $id);
		$viewer->assign('BLOCKITEMNUM', $num);
		$viewer->assign('LANG', $langfield);
		$viewer->assign('LANGP', $langp);
		$viewer->assign('LANGV', $value);
        $viewer->view('BlockEdition.tpl', 'OSSMenuManager');
		
	}

	
}