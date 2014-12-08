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

class Settings_OSSMenuManager_Configuration_View extends Settings_Vtiger_Index_View {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if(!$permission) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}
    
    /**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
            'layouts.vlayout.modules.OSSMenuManager.resources.general'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function process(Vtiger_Request $request) {
		global $currentModule, $log;
        $adb = PearDatabase::getInstance();        
        $viewer = $this->getViewer($request);
        $recordModel = Vtiger_Record_Model::getCleanInstance( $currentModule );        
        
        require_once("modules/$currentModule/$currentModule.php");
        
        //$mode = $request->get( 'mode' );
        //$id = $request->get( 'id' );
        //$create_execute = $request->get( 'create_execute' );
        //$delete = $request->get( 'delete' );
        //$edit_execute = $request->get( 'edit_execute' );
        
        /*$moduleInMenuSql = "SELECT tabid FROM vtiger_ossmenumanager_parenttabrel";
		$moduleInMenuResult = $adb->query($moduleInMenuSql);
		$moduleInMenuNum = $adb->num_rows($moduleInMenuResult);
		
		for($i = 0; $i < $moduleInMenuNum; $i++) {			
			$tabid = $adb->query_result($moduleInMenuResult, $i, 'tabid');
			$moduleExists = "SELECT name FROM vtiger_tab WHERE tabid = '$tabid'";
			$moduleExistsResult = $adb->query($moduleExists);
			$moduleExistsNum = $adb->num_rows($moduleExistsResult);
			
			if($moduleExistsNum == 0) {
				$deleteModuleSql = "DELETE FROM vtiger_ossmenumanager_parenttabrel WHERE tabid = '$tabid'";
				$adb->query($deleteModuleSql);
			}		
		}*/
        
        /* if ( $mode == 'delete' ) {
            $sql = "DELETE FROM `vtiger_ossmenumanager_customlinks` WHERE `id` = ?;";
            $error = "Error while deleting records to the table vtiger_ossmenumanager_customlinks. File OSSMenuManager.php";
            $adb->pquery( $sql, array($id), true, $error );
        }
        else if ( !empty($edit_execute) ) {        
            $url = $request->get( 'url' );
            $url2 = $request->get( 'url2' );
            $label = $request->get( 'label' );
            $sequence = $request->get( 'sequence' );
            $selected_profiles = $request->get( 'rights' );
            $new_window = $request->get( 'new_window' );
            $rights = $selected_profiles !== '' ? implode( ',', $selected_profiles ) : '';
            $parentid = $request->get( 'skrot_menu' );
            $typ = intval($request->get( 'typ_menu' ));            
            
            switch( $typ ) {
                case 1:   // Etykieta 
                    $url = '*etykieta*';
                    break;
                case 2: // Separator';
                    $url = '*separator*';
                    break;
                case 3: // skrypt';
                    $url = $url2;
                    break;
            }
            
            $sql = "UPDATE `vtiger_ossmenumanager_customlinks` SET `url` = ?, `label` = ?, `sequence` = ?, `rights` = ?, `new_window` = ?, `parenttabid` = ?, `type` = ? WHERE `id` = ?";
            $error = "Error when updating records in the table vtiger_ossmenumanager_customlinks. File OSSMenuManager.php";
            $params = array(
                $url,
                $label,
                $sequence,
                $rights,
                $new_window,
                $parentid,
                $typ,
                $id
            );
            
            $adb->pquery( $sql, $params, true, $error );
            $mode = '';
        }
        else if ( !empty($create_execute) ) {
            $url = $request->get( 'url' );
            $url2 = $request->get( 'url2' );
            $label = $request->get( 'label' );
            $sequence = $request->get( 'sequence' );
            $selected_profiles = $request->get( 'rights' );
            $new_window = $request->get( 'new_window' );
            $rights = $selected_profiles !== '' ? implode( ',', $selected_profiles ) : '';
            $parentid = $request->get( 'skrot_menu' );
            $typ = intval($request->get( 'typ_menu' ));
            
            switch( $typ ) {
                case 1:   // Etykieta 
                    $url = '*etykieta*';
                    break;
                case 2: // Separator';
                    $url = '*separator*';
                    break;                
                case 3: // skrypt';
                    $url = $url2;
                    break;
            }
            
            $sql = "INSERT INTO `vtiger_ossmenumanager_customlinks` (`url`, `label`, `sequence`, `rights`, `new_window`, `parenttabid`, `type` ) VALUES (?, ?, ?, ?, ?, ?, ?);";
            $error = "Error when inserting records into a table vtiger_ossmenumanager_customlinks. File OSSMenuManager.php";
            $params = array(
                $url,
                $label,
                $sequence,
                $rights,
                $new_window,
                $parentid,
                $typ
            );
            
            $adb->pquery( $sql, $params, true, $error );
            
            $mode = '';
        }
        
        if ( $mode == 'edit' ) {
            $sql = "SELECT * FROM `vtiger_ossmenumanager_customlinks` WHERE `id` = ?";
            $error = "Error retrieving data from the table vtiger_ossmenumanager_customlinks. File OSSMenuManager.php";
            $pobierz = $adb->pquery( $sql, array($id), true, $error );
            
            $viewer->assign( 'PARENTTABID', $adb->query_result( $pobierz, 0, 'parenttabid' ) );
            $viewer->assign( 'TYP', $adb->query_result( $pobierz, 0, 'type' ) );            
        }
        else { 	
            $sql = "SELECT * FROM `vtiger_ossmenumanager_customlinks`";
            $error = "Error retrieving data from the table vtiger_ossmenumanager_customlinks. File OSSMenuManager.php";
            $pobierz = $adb->query( $sql, true, $error ); 
        }
    
		$data = array();
		if( $adb->num_rows( $pobierz ) > 0 )
		{
			for( $i=0; $i< $adb->num_rows( $pobierz ); $i++ )
			{
                $typ = $adb->query_result( $pobierz, $i, "type" );
                $nazwa = $adb->query_result( $pobierz, $i, "label" );
                $url = $adb->query_result( $pobierz, $i, "url" );
                
                switch( $typ ) {
                    case 0: 
                        $typ = 'Skrót';
                        break;
                    case 1: 
                        $typ = 'Etykieta';
                        $url = '';
                        break;
                    case 2: 
                        $typ = 'Separator';
                        $nazwa = '';
                        $url = '';
                        break;                    
                    case 3: 
                        $typ = 'Skrypt';
                        break;
                }
                
                $data[$i+1]['type'] = $typ;
                
				$data[$i+1]['id'] = $adb->query_result( $pobierz, $i, "id" );
				$data[$i+1]['url'] = $url;
				$data[$i+1]['label'] = $nazwa;
				$data[$i+1]['sequence'] = $adb->query_result( $pobierz, $i, "sequence" );
				$data[$i+1]['new_window'] = $adb->query_result( $pobierz, $i, "new_window" );
                $data[$i+1]['parenttabid'] = $adb->query_result( $pobierz, $i, "parenttabid" );  
                
                // pobierz nazwę menu nadrzędnego
                $sql = "SELECT `parenttab_label` FROM `vtiger_ossmenumanager_parenttab` WHERE `parenttabid` = ?;";
                $menuResult = $adb->pquery( $sql, array($data[$i+1]['parenttabid']), true );
                $data[$i+1]['parent_label'] = $adb->query_result( $menuResult, 0, 'parenttab_label' );
				
				$rights = explode( ',', $adb->query_result( $pobierz, $i, "rights" ) );
				if($mode == 'edit') {
                    $data[$i+1][ 'rights' ] = $rights;
				}
				else {
					$nazwy = '';
					
					foreach( $rights as $nazwa ) {
                        $nazwaProfilu = $recordModel->getProfileName( $nazwa );
						$nazwy .= " $nazwaProfilu,";
					}
					$nazwy = trim($nazwy, ',' );
					$data[$i+1][ 'rights' ] = $nazwy;
				}
			}
		} 
        else { 
            $viewer->assign( 'no_db', 'yes' );
        }         */
		//$viewer->assign( 'data', $data );
        //$profiles = $recordModel->getAllProfiles();
        $userProfile = $recordModel->getCurrentUserProfile();
        //-----------------------------//
		//$recordModel->load_default_menu();
	//	$modulestab = $adb->query( "SELECT tablabel FROM vtiger_tab", true );
		
		
		$Module = 'OSSMenuManager';
		$sql = "SELECT * FROM vtiger_ossmenumanager ORDER BY parent_id,sequence";
		$result = $adb->pquery( $sql, array(), true );
		$data_array = $result->GetArray();
		
		foreach( $data_array as $indeks => $menu ){
			
			if( $menu['parent_id'] == 0 ) {
				$permission ='no';
				$accesProfile = explode(' |##| ',$menu['permission']);
				foreach ($accesProfile as $acces){
					foreach ($userProfile as $profile){
						if (settype($acces, "integer") == $profile || empty($acces)) {
							$permission = 'yes';
						}
					}
				}
				$menu_array['group'][$menu['sequence']] = array( 
                    'id'            => $menu['id'],
                    'label'         => vtranslate( $menu['label'], $Module ),
                    'visible'       => $menu['visible'],
                    'permission'    => $permission,
					'locationicon'  => $menu['locationicon'],
					'sizeicon'		=> $menu['sizeicon'],
					'langfield'		=> $menu['langfield']
                );
			
/*	
	
		echo '<pre>';print_r($menu['langfield']);echo '</pre>';exit;

*/			if(	$menu['langfield']){
				$res = explode('#',$menu['langfield']);
				for ($i=0; count($res)>$i; $i++){
					$prefix=substr( $res[$i], 0, strpos($res[$i], "*") );
					$value=substr( $res[$i], 6 );
					if(Users_Record_Model::getCurrentUserModel()->get('language')==$prefix){
					//	echo '<pre>';print_r($prefix);echo '</pre>';exit;
						$menu_array['group'][$menu['sequence']]['label']=$value;
					}
				}
			}				
				
				
				
				
				$sizeicon_first = substr( $menu['sizeicon'], 0, strpos($menu['sizeicon'], "x") );
				$sizeicon_second = substr( $menu['sizeicon'], 3, 5 );
				$menu_array['group'][$menu['sequence']]['iconf'] = $sizeicon_first;
				$menu_array['group'][$menu['sequence']]['icons'] = $sizeicon_second;
				
			}
            else {
				$permission ='no';
				$accesProfile = explode(' |##| ',$menu['permission']);
				foreach ($accesProfile as $acces){
					foreach ($userProfile as $profile){
						if (settype($acces, "integer") == $profile || empty($acces)) {
							$permission = 'yes';
						}
					}
				}
				$menu_array['menu'][$menu['parent_id']][$menu['sequence']] = array(
                    'id'            => $menu['id'],
                    'label'         => vtranslate( $menu['label'], $Module ),
                    'tabid'         => $menu['tabid'],
                    'type'          => $menu['type'],
                    'url'           => $menu['url'],
                    'new_window'    => $menu['new_window'],
                    'visible'       => $menu['visible'],
                    'permission'    => $permission,
					'locationicon'  => $menu['locationicon'],
					'sizeicon'		=> $menu['sizeicon']
                );
				
				if(	$menu['langfield']){
				$res = explode('#',$menu['langfield']);
				for ($i=0; count($res)>$i; $i++){
					$prefix=substr( $res[$i], 0, strpos($res[$i], "*") );
					$value=substr( $res[$i], 6 );
					if(Users_Record_Model::getCurrentUserModel()->get('language')==$prefix){
					
						$menu_array['menu'][$menu['parent_id']][$menu['sequence']]['label']=$value;
				//		echo '<pre>';print_r($menu_array['menu'][$menu['parent_id']][$menu['sequence']]['label']);echo '</pre>';exit;
						}
					}
				}
				
				$sizeicon_first = substr( $menu['sizeicon'], 0, strpos($menu['sizeicon'], "x") );
				$sizeicon_second = substr( $menu['sizeicon'], 3, 5 );
				$menu_array['menu'][$menu['parent_id']][$menu['sequence']]['iconf'] = $sizeicon_first;
				$menu_array['menu'][$menu['parent_id']][$menu['sequence']]['icons'] = $sizeicon_second;
			}
		}
		

		
		
		
		
		
		
	//	echo '<pre>';print_r($menu_array);echo '</pre>';exit;
		$viewer->assign('MENUSTRUKTURE', $menu_array );
	//	$viewer->assign('MODULESTAB', $modulestab->GetArray());
        $viewer->assign('MODULENAME', $currentModule);
        $viewer->assign('MODE', $mode);  
        $viewer->assign('PROFILES', $profiles);          
        $viewer->assign('recordid', $id);  //

        /* $OSSMenuManager = new OSSMenuManager();
        $OSSMenuManager->getMenuStructure();
        
        // pobierz główne menu
        $sql = "SELECT * FROM `vtiger_ossmenumanager_parenttab` ORDER BY `sequence` ASC;";
        $wynik = $adb->pquery( $sql, array(), true );
        $num = $adb->num_rows( $wynik );
        $menu = array();
        for ($i=0; $i<$num; $i++ ) {
            $pid = $adb->query_result( $wynik, $i, 'parenttabid' );
            $plabel = $adb->query_result( $wynik, $i, 'parenttab_label' );
            array_push( $menu, array($pid, $plabel) );
        }
        $viewer->assign( 'MAINMENU', $menu ); */
        
        //$viewer->assign( "MENUTAB", $OSSMenuManager->menutab );
		/* $viewer->assign( 'LBL_YOU_MUST_SPECIFY_THE_NAME', vtranslate( 'LBL_YOU_MUST_SPECIFY_THE_NAME', $currentModule ) );
		$viewer->assign( 'LBL_LOADING', vtranslate( 'LBL_LOADING', $currentModule ) );
		$viewer->assign( 'LBL_REFRESH_MENU', vtranslate( 'LBL_REFRESH_MENU', $currentModule ) );
		$viewer->assign( 'LBL_OSSMENUMANAGER', vtranslate( 'LBL_OSSMENUMANAGER', $currentModule ) );
        $viewer->assign( 'LBL_UP', vtranslate( 'LBL_UP', $currentModule ) );
		$viewer->assign( 'LBL_DOWN', vtranslate( 'LBL_DOWN', $currentModule ) ); */
		$viewer->assign( 'USER_PROFILE', $userProfile );
		
        $uninstall = $request->get( 'uninstall' );
        $status = $request->get( 'status' );
        if ( !empty($uninstall) && !empty($status) ) {            
            $log->debug( 'Uninstallation started...' );
            $moduleName = $request->getModule();
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            if($moduleModel) {
                $moduleModel->delete();
            }
        }
        else if ( !empty($uninstall) && empty($status) ) { 
            $errorMsg = 'MSG_ERROR_STATUS';
        }
        $viewer->assign( 'ERROR', $errorMsg );
		
		// paintedicon
		$paintedIconValue = $adb->query_result( $result, 0, 'paintedicon' );
		$viewer->assign( 'PAINTEDICON', $paintedIconValue );	
        $viewer->view('Configuration.tpl', 'OSSMenuManager');
	}
}
?>
