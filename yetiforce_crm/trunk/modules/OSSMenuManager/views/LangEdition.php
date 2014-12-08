<?php
require_once 'include/utils/VtlibUtils.php';

class OSSMenuManager_LangEdition_View extends Vtiger_Edit_View {
	public function checkPermission(Vtiger_Request $request) {

	}
	public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
	    $recordModel = Vtiger_Record_Model::getCleanInstance( 'OSSMenuManager' ); 
        
        $id = $request->get( 'id' );

		$langfield=vtlib_getToggleLanguageInfo();
		
		$blockRecord = $recordModel->getMenuRecord( $id );
		$res = explode('#',$blockRecord[langfield]);
				for ($i=0; count($res)>$i; $i++){
					$prefix=substr( $res[$i], 0, strpos($res[$i], "*") );
					$value[$prefix]=substr( $res[$i], 6 );
					}

        $viewer = $this->getViewer($request);
		$viewer->assign('LANG', $langfield);
	    $viewer->assign('MENUID', $id);
		$viewer->assign('LANGV', $value);
        $viewer->view('LangEdition.tpl', 'OSSMenuManager');
	}
}
?>
