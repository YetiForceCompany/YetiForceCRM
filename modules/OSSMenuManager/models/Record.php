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
class OSSMenuManager_Record_Model extends Vtiger_Record_Model {

	//////////////////////////////////////////////////////////
	public $types_menu = array(
        '0' => 'LBL_module',
        '1' => 'LBL_shortcut',
        '2' => 'LBL_label',
        '3' => 'LBL_separator',
        '4' => 'LBL_script'
    );
    
    public static function getMenu() {
        $db = PearDatabase::getInstance();
        $profileId = self::getCurrentUserProfile();
        
        $userModel = Users_Record_Model::getCurrentUserModel();
        $isAdmin = is_admin( $userModel );
        
        if ( $isAdmin ) {
            $sql = "SELECT * FROM `vtiger_ossmenumanager` WHERE `parent_id` = ? AND `visible` = ? ORDER BY `sequence` ASC;";
            $params = array( 0, 1 );	
        }
        else {
            $sql = "SELECT * FROM `vtiger_ossmenumanager` WHERE `parent_id` = ? AND `visible` = ? AND (";
			if(!empty($profileId)){
				for($i=0; $i<count($profileId); $i++){
					$sql .= "`permission` RLIKE ' " . $profileId[$i] . " ' OR ";
				}
			}
			$sql .= " `permission` = '' OR `permission` = '0') ORDER BY `sequence` ASC;";
            $params = array( 0, 1 );
	//	echo '<pre>';print_r($num);echo '</pre>';
		}
		
        $result = $db->pquery( $sql, $params, true );
		$num = $db->num_rows( $result );
        
        $menuStructure = array();
		$breadcrumbs = array();
		$request = new Vtiger_Request($_REQUEST, $_REQUEST);
		
        if ( $num > 0 ) {
            for ( $i=0; $i<$num; $i++ ) {
                $id = $db->query_result( $result, $i, 'id' );
				$locationicon = $db->query_result( $result, $i, 'locationicon' );
				$sizeicon 	= $db->query_result( $result, $i, 'sizeicon');
				$name = $db->query_result( $result, $i, 'label' );	
				$sizeicon_first = substr( $sizeicon, 0, strpos($sizeicon, "x") );
				$sizeicon_second = substr( $sizeicon, 3, 5 );
				$langfied = $db->query_result( $result, $i, 'langfield' );
                
				if(	$langfied ){
					$res = explode('#',$langfied);
					for ($k=0; count($res)>$k; $k++){
						$prefix=substr( $res[$k], 0, strpos($res[$k], "*") );
						$value=substr( $res[$k], 6 );
						if(Users_Record_Model::getCurrentUserModel()->get('language')==$prefix){
					//		echo '<pre>';print_r($_matches[1][$k]);echo '</pre>';exit;
							$name=$value;
						}
				
					}
				}
					
				$menuStructure[$name] = array();
                
                 if ( $isAdmin ) {
                    $subSql = "SELECT * FROM `vtiger_ossmenumanager` WHERE `parent_id` = ? AND `visible` = ? ORDER BY `sequence` ASC;";
                    $subParams = array( $id, 1 );
                }
                else {
                    $subSql = "SELECT * FROM `vtiger_ossmenumanager` WHERE `parent_id` = ? AND `visible` = ? AND (";
					if(!empty($profileId)){
						for($k=0; $k<count($profileId); $k++){
						$subSql .= "`permission` RLIKE ' " . $profileId[$k] . " ' OR ";
						}
					}
					$subSql .= " `permission` = '' OR `permission` = '0') ORDER BY `sequence` ASC;";
					$subParams = array( $id, 1 );
                }
                $subResult = $db->pquery( $subSql, $subParams, true );
                $subNum = $db->num_rows( $subResult );

                if ( $subNum > 0 ) {
                    for ( $j=0; $j<$subNum; $j++ ) {
                        $subName    		= $db->query_result( $subResult, $j, 'label' );
						$subNameOrg    		= $subName;
                        $type       		= $db->query_result( $subResult, $j, 'type' );
                        $tabId      		= $db->query_result( $subResult, $j, 'tabid' );
                        $newWindow  		= $db->query_result( $subResult, $j, 'new_window' );
						$locationiconname 	= $db->query_result( $subResult, $j, 'locationicon');
						$subsizeicon 		= $db->query_result( $subResult, $j, 'sizeicon');
                        $subsizeicon_first 	= substr($subsizeicon, 0, strpos($subsizeicon, "x"));
						$subsizeicon_second	= substr($subsizeicon, 3, 5);
						$langfiedpick 		= $db->query_result( $subResult, $j, 'langfield' );
						
						if(	$langfiedpick && (intval($type)==0) ){
							$res = explode('#',$langfiedpick);
							for ($k=0; count($res)>$k; $k++){
								$prefix=substr( $res[$k], 0, strpos($res[$k], "*") );
								$value=substr( $res[$k], 6 );
							//	echo '<pre>';print_r($value);echo '</pre>';exit;
								if(Users_Record_Model::getCurrentUserModel()->get('language')==$prefix){
									$subName=$value;
								}
							}
						}elseif($langfiedpick){
							$res = explode('#',$langfiedpick);
							for ($k=0; count($res)>$k; $k++){
								$prefix=substr( $res[$k], 0, strpos($res[$k], "*") );
								$value=substr( $res[$k], 6 );
							//	echo '<pre>';print_r($value);echo '</pre>';exit;
								if(Users_Record_Model::getCurrentUserModel()->get('language')==$prefix){
									$subName=$value;
								}
							}
						}
                        if ( $newWindow == 1 )
                            $newWindow = '*_blank*';
                        else
                            $newWindow = '';
                        
                        switch( intval($type) ) {
                            case 0:
                                $model = Vtiger_Module_Model::getInstance( $subNameOrg );
								if ( $model )
									$url = $model->getDefaultUrl();
								else {
									// usuń nieistniejącą pozycję
									$recordModel = Vtiger_Record_Model::getCleanInstance( 'OSSMenuManager' );
									$recordModel->deleteMenu( $subId );
									$url = false;
								}
                                break;
								
                            case 1:
                            case 2:
                            case 3:
                            case 4:
                            default:
                                $url = $db->query_result_raw( $subResult, $j, 'url' );
                        }

						if ( $url !== false ) {
							$url = $newWindow.$url;
							$menuStructure[$name][$j] = array( 'name' => $subName,'mod' => $subNameOrg, 'link' => $url, 'sizeicon_first' => $subsizeicon_first, 'sizeicon_second' => $subsizeicon_second, 'locationiconname' => $locationiconname);
						}
						
						$moduleName = Vtiger_Functions::getModuleName($tabId);
						$excludedViews = array("DashBoard",'index','Index');
						$purl = false;
						
						if ( $request->get('module') != '' && $request->get('module') == $moduleName && vglobal('breadcrumbs') && $request->get('parent') == '') {
							$breadcrumbs[] = array('lable' => vtranslate($name, 'OSSMenuManager'));
							$breadcrumbs[] = array('lable' => vtranslate($subName, $moduleName), 'url' => $url, 'class' => 'moduleColor_'.$moduleName );
							if ( $request->get('view') == 'Edit' && $request->get('record') == '' ) {
								$breadcrumbs[] = array('lable' => vtranslate('LBL_VIEW_CREATE', $moduleName) );
							}elseif(!in_array($request->get('view'), $excludedViews)){
								$breadcrumbs[] = array('lable' => vtranslate('LBL_VIEW_'.strtoupper($request->get('view')), $moduleName) );
							}
							if( $request->get('record') != '' ){
								$recordLabel = Vtiger_Functions::getCRMRecordLabel( $request->get('record') );
								if ( $recordLabel != '' ) {
									$breadcrumbs[] = array('lable' => $recordLabel );
								}
							}
						}elseif( vglobal('breadcrumbs') && $request->get('module') != '' && $request->get('parent') == ''){
							$parts = parse_url($url);
							parse_str($parts['query'], $purl);
							if( $request->get('module') == $purl['module'] && $request->get('view') == $purl['view'] && $request->get('viewname') == $purl['viewname'] ){
								$breadcrumbs[] = array('lable' => vtranslate($name, 'OSSMenuManager'));
								$breadcrumbs[] = array('lable' => vtranslate($request->get('module'), $request->get('module')), 'url' => 'index.php?module='.$request->get('module').'&view=List' , 'class' => 'moduleColor_'.$request->get('module'));
								if($request->get('view') != 'List'){
									$breadcrumbs[] = array('lable' => vtranslate($subName, $moduleName), 'url' => $url);
								}
							}
						}
                    }
                }
				
				$menuStructureGroupe[$name]['iconf'] = $sizeicon_first;
				$menuStructureGroupe[$name]['picon'] = $locationicon;			
				$menuStructureGroupe[$name]['icons'] = $sizeicon_second;
            }
			if( vglobal('breadcrumbs') && count($breadcrumbs) == 0 && $request->get('module') != '' && $request->get('parent') == ''){
				if('Users' == $request->get('module')){
					$moduleModel = Vtiger_Module_Model::getInstance($request->get('module'));
					$listViewUrl = $moduleModel->getListViewUrl();
					$breadcrumbs[] = array('lable' => vtranslate($request->get('module'), $request->get('module')), 'url' => $listViewUrl, 'class' => 'moduleColor_'.$request->get('module'));
				}
				else
					$breadcrumbs[] = array('lable' => vtranslate($request->get('module'), $request->get('module')), 'url' => 'index.php?module='.$request->get('module').'&view=List', 'class' => 'moduleColor_'.$request->get('module'));

				if ( $request->get('view') == 'Edit' && $request->get('record') == '' ) {
					$breadcrumbs[] = array('lable' => vtranslate('LBL_VIEW_CREATE', $request->get('module')) );
				}else{
					$breadcrumbs[] = array('lable' => vtranslate('LBL_VIEW_'.strtoupper($request->get('view')), $request->get('module')) );
				}
				if( $request->get('record') != '' ){
					$recordLabel = Vtiger_Functions::getCRMRecordLabel( $request->get('record') );
					if ( $recordLabel != '' ) {
						$breadcrumbs[] = array('lable' => $recordLabel );
					}
				}
			}			
            return array('structure'=>$menuStructure,'icons'=>$menuStructureGroupe,'breadcrumbs'=>$breadcrumbs);
        }
        else 
            return array();
    }
	
	
     public function getCurrentUserProfile() {        
        $userModel = Users_Record_Model::getCurrentUserModel();
        $roleId = $userModel->getRole();

        $db = PearDatabase::getInstance();
        $sql = "SELECT `profileid` FROM `vtiger_role2profile` WHERE `roleid` = ?;";
        $params = array( $roleId );
        $result = $db->pquery( $sql, $params, true );

        // nowy kod
		$profileId = array();
		$num = $db->num_rows( $result );
		for($i=0; $i<$num; $i++){
			$profileId[] = $db->query_result( $result, $i, 'profileid' );
		}
//echo '<pre>';print_r($profileId);echo '</pre>';		
        return $profileId;
        // nowy kod
    }
   
    public function addMenu( $params ) {
        $adb = PearDatabase::getInstance();        
        
        if ( intval($params['sequence']) == -1 )
            $params['sequence'] = self::getSequence( $params['parent_id'] );
        
        $sql = "INSERT INTO `vtiger_ossmenumanager` (`parent_id`,`tabid`,`label`,`sequence`,`visible`,`type`,`url`,`new_window`,`permission`, `locationicon`, `sizeicon`, `langfield` ) values (?,?,?,?,?,?,?,?,?,?,?,?);";        
        $parametry = array( 
            $params['parent_id'], 
            $params['tabid'], 
            $params['label'], 
            $params['sequence'], 
            $params['visible'], 
            $params['type'], 
            $params['url'], 
            $params['new_window'], 
            $params['permission'],
			$params['locationicon'],
			$params['sizeicon'],
			$params['langfield']
        );
		$adb->pquery( $sql, $parametry, true );
        
		return intval( $adb->getLastInsertID() );
    }
    
    public function editMenu( $params ) {
        $adb = PearDatabase::getInstance();     
        $params['locationicon'] = trim( $params['locationicon']);
        $sql = "UPDATE `vtiger_ossmenumanager` SET `tabid` = ?, `label` = ?, `visible` = ?, `url` = ?, `new_window` = ?, `permission` = ?, `locationicon` = ?, `sizeicon` = ? WHERE `id` = ? LIMIT 1;";        
        $parametry = array( 
            $params['tabid'], 
            $params['label'],
            $params['visible'], 
            $params['url'], 
            $params['new_window'], 
            $params['permission'],
			$params['locationicon'],
			$params['sizeicon'],
            $params['id']

        );
        
		$adb->pquery( $sql, $parametry, true );
        
		return true;
    }
    
	
	public function editLang( $params ) {
        $adb = PearDatabase::getInstance();     
 
        $sql = "UPDATE `vtiger_ossmenumanager` SET  `langfield`  = ? WHERE `id` = ? LIMIT 1;";        
        $parametry = array( 

			$params['langfield'],	
            $params['id']

        );
        
		$adb->pquery( $sql, $parametry, true );
        
		return true;
    }
	
	
	public function updateIcon( $paintedicon ) {
        $adb = PearDatabase::getInstance();     
 
        $sql = "UPDATE `vtiger_ossmenumanager` SET  `paintedicon`  = ? ;";        
        $parametry = array( 
			$paintedicon
        );
        
		$adb->pquery( $sql, $parametry, true );
        
		return true;
    }
	public function getIcon() {
        $adb = PearDatabase::getInstance();     
 
        $sql = "SELECT * FROM `vtiger_ossmenumanager`";        
        $parametry = array( );
		$result = $adb->pquery( $sql, $parametry, true );
		return $adb->query_result( $result, 0, 'paintedicon' );
    }
    /*
    * usuwa wybraną pozycję menu
    * @id - numer id rekordu menu
    * @return true lub false
    */
    public function deleteMenu( $id ) {
        $adb = PearDatabase::getInstance();  
        
        $sql = "SELECT `parent_id` FROM `vtiger_ossmenumanager` WHERE `id` = ? LIMIT 1;";        
        $params = array( intval($id) );
        $result = $adb->pquery( $sql, $params, true );
        $blockId = $adb->query_result( $result, 0, 'parent_id' );
        
        $sql = "DELETE FROM `vtiger_ossmenumanager` WHERE `id` = ? LIMIT 1;";        
        $params = array( intval($id) );
        $result = $adb->pquery( $sql, $params, true );
        
        if ( $adb->getAffectedRowCount( $result ) ) {
            $this->recalculateMenuSequence( $blockId );
            return true;
        }
        else
            return false;
    }
    
    /*
    * przelicza od nowa numery sekwencji
    * @blockId - pozycje menu jakigo bloku przeliczyć
    * @retun - true lub false
    */
    public function recalculateMenuSequence( $blockId ) {
        $adb = PearDatabase::getInstance();  
        
        $sql = "SELECT `id` FROM `vtiger_ossmenumanager` WHERE `parent_id` = ? ORDER BY `sequence` ASC;;";        
        $params = array( intval($blockId) );
        $result = $adb->pquery( $sql, $params, true );
        $num = $adb->num_rows( $result );
        
        for ( $i=0; $i<$num; $i++ ) {
            $seqId = $adb->query_result( $result, $i, 'id' );
            
            $updateSql = "UPDATE `vtiger_ossmenumanager` SET `sequence` = ? WHERE `id` = ? LIMIT 1;";
            $updateParams = array( $i+1, $seqId );
            $adb->pquery( $updateSql, $updateParams, true );
        }
        
        return true;
    }
    
    /*
    * dodaje nowy blok menu
    * @params - parametry nowego bloku
    * @return - true lub false
    */
    public function addBlock( $params ) {
        $adb = PearDatabase::getInstance();        
        $params['name'] = trim( $params['name'] );
		$params['locationicon'] = trim( $params['locationicon']);
        
        if ( strlen($params['name']) == 0 )
            return false;
            
        $sql = "SELECT COUNT(1)+1 as nr FROM `vtiger_ossmenumanager` WHERE `parent_id` = 0;";
        $result = $adb->query( $sql, true );
        $nextSeq = $adb->query_result( $result, 0, 'nr' );
        
        $sql = "INSERT INTO `vtiger_ossmenumanager` (`parent_id`,`tabid`,`label`,`sequence`,`visible`,`type`,`url`,`new_window`,`permission`,`locationicon`,`sizeicon`, `langfield`) values (?,?,?,?,?,?,?,?,?,?,?,?);";        
        $parametry = array( 
            0, 
            0, 
            $params['name'], 
            $nextSeq, 
            $params['visible'], 
            0, 
            '', 
            0, 
            $params['permission'],
			$params['locationicon'],
			$params['sizeicon'],
			$params['langfield']
        );
        
		$adb->pquery( $sql, $parametry, true );
        
		return intval( $adb->getLastInsertID() );
    }
    
    /*
    * edytuje blok menu
    * @params - parametry bloku
    * @return - true lub false
    */
    public function editBlock( $params ) {
        $adb = PearDatabase::getInstance();        
        $params['name'] = trim( $params['name'] );
		$params['locationicon'] = trim( $params['locationicon']);
		 $params['langfield'] = trim( $params['langfield']);
        
        if ( strlen($params['name']) == 0 )
            return false;
            
        $sql = "SELECT COUNT(1)+1 as nr FROM `vtiger_ossmenumanager` WHERE `parent_id` = 0;";
        $result = $adb->query( $sql, true );
        $nextSeq = $adb->query_result( $result, 0, 'nr' );
        
        $sql = "UPDATE `vtiger_ossmenumanager` SET `label` = ?, `visible` = ?, `permission` = ?, `locationicon` = ?, `sizeicon` = ?, `langfield` = ? WHERE `id` = ? LIMIT 1;";        
        $parametry = array( 
            $params['name'],
            $params['visible'], 
            $params['permission'],
			$params['locationicon'],
			$params['sizeicon'], 
			$params['langfield'],
			$params['id']
			
        );
   
		$adb->pquery( $sql, $parametry, true );
        
	//	if ( $adb->getAffectedRowCount( $result ) )
            return true;
    //    else
      //      return false;
    }
    
    /*
    * usuwa blok menu
    * @id - numer bloku menu
    * @return true lub false
    */
    public function deleteBlock( $id ) {
        $adb = PearDatabase::getInstance();  
        
        $sql = "DELETE FROM `vtiger_ossmenumanager` WHERE `id` = ? LIMIT 1;";        
        $params = array( intval($id) );
        $result = $adb->pquery( $sql, $params, true );
        
        if ( $adb->getAffectedRowCount( $result ) ) {
            $this->recalculateMenuSequence( 0 );
            return true;
        }
        else
            return false;
    }
    
    public function updateBlocks($newSequence) {
        $adb = PearDatabase::getInstance();
		foreach( $newSequence as $blockID => $row ){
			$adb->query( "update vtiger_ossmenumanager set sequence = '$row' WHERE id = '$blockID'", true );
		}
		return vtranslate( 'MSG_SAVEBLOCK', 'OSSMenuManager' );
    }
    public function updateFields( $newSequence ) {
        $adb = PearDatabase::getInstance();
		foreach( $newSequence as $block ){
			$adb->query( "update vtiger_ossmenumanager set parent_id = '".$block['block']."',sequence = '".$block['sequence']."' WHERE id = '".$block['fieldId']."'", true );
		}
		return vtranslate( 'MSG_SAVEFIELDS', 'OSSMenuManager' );
    }
    
    // pobiera kolejny wolny numer id w kolejności
    public function getSequence( $parentId ) {
        $db = PearDatabase::getInstance();
        $sql = "SELECT MAX(sequence)+1 as seq FROM `vtiger_ossmenumanager` WHERE `parent_id` = ?;";
        $params = array( $parentId );
        $result = $db->pquery( $sql, $params, true );
        $seq = $db->query_result( $result, 0, 'seq' );

        return intval( $seq );
    }
    
    // pobiera informacje o pozycji menu po id
    public function getMenuRecord( $id ) {
        $db = PearDatabase::getInstance();
        
        $sql = "SELECT * FROM `vtiger_ossmenumanager` WHERE `id` = ? LIMIT 1;";
        $params = array( $id );
        $result = $db->pquery( $sql, $params, true );
        
        $num = $db->num_rows( $result );
        
        if ( $num == 0 )
            return false;
        
        return array_shift( $result->GetArray() );
    }    
}
