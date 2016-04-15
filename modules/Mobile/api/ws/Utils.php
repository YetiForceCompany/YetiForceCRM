<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
class Mobile_WS_Utils {
	/*
	static function initAppGlobals() {
		global $current_language, $app_strings, $app_list_strings, $app_currency_strings;
		$current_language = 'en_us';
		
		$app_currency_strings = return_app_currency_strings_language($current_language);
		$app_strings = return_application_language($current_language);
		$app_list_strings = return_app_list_strings_language($current_language);
	}
	
	static function initModuleGlobals($module) {
		global $mod_strings, $current_language;
		if(isset($current_language)) {
			$mod_strings = return_module_language($current_language, $module);
		}
	}*/
	
	static function getVtigerVersion() {
		global $YetiForce_current_version;
		return $YetiForce_current_version;
	}
	
	static function getVersion() {
		$adb = PearDatabase::getInstance();
		$versionResult = $adb->pquery("SELECT version FROM vtiger_tab WHERE name='Mobile'", array());
		return $adb->query_result($versionResult, 0, 'version');
	}
	
	static function array_replace($search, $replace, $array) {
		$index = array_search($search, $array);
		if($index !== false) {
			$array[$index] = $replace;
		}
		return $array;
	}
	
	static function getModuleListQuery($moduleName, $where = '1=1') {
		$module = CRMEntity::getInstance($moduleName);
		return $module->create_list_query('', $where);
	}
	
	static $moduleWSIdCache = array();
	
	static function getEntityModuleWSId($moduleName) {
		
		if (!isset(self::$moduleWSIdCache[$moduleName])) {
			$adb = PearDatabase::getInstance();
			$result = $adb->pquery("SELECT id FROM vtiger_ws_entity WHERE name=?", array($moduleName));
			if ($result && $adb->num_rows($result)) {
				self::$moduleWSIdCache[$moduleName] = $adb->query_result($result, 0, 'id');
			}
		}
		return self::$moduleWSIdCache[$moduleName];
	}
	
	static function getEntityModuleWSIds($ignoreNonModule = true) {
		$adb = PearDatabase::getInstance();
		
		$modulewsids = array();
		$result = false;
		if($ignoreNonModule) {
			$result = $adb->pquery("SELECT id, name FROM vtiger_ws_entity WHERE ismodule=1", array());
		} else {
			$result = $adb->pquery("SELECT id, name FROM vtiger_ws_entity", array());
		}
		
		while($resultrow = $adb->fetch_array($result)) {
			$modulewsids[$resultrow['name']] = $resultrow['id'];
		}
		return $modulewsids;
	}
	
	static function getEntityFieldnames($module) {
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT fieldname FROM vtiger_entityname WHERE modulename=?", array($module));
		$fieldnames = array();
		if($result && $adb->num_rows($result)) {
			$fieldnames = explode(',', $adb->query_result($result, 0, 'fieldname'));
		}
		switch($module) {
			case 'HelpDesk': $fieldnames = self::array_replace('title', 'ticket_title', $fieldnames); break;
			case 'Document': $fieldnames = self::array_replace('title', 'notes_title', $fieldnames); break;
		}
		return $fieldnames;
	}
	
	static function getModuleColumnTableByFieldNames($module, $fieldnames) {
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT fieldname,columnname,tablename FROM vtiger_field WHERE tabid=? AND fieldname IN (".
			generateQuestionMarks($fieldnames) . ")", array(getTabid($module), $fieldnames)
		);
		$columnnames = array();
		if ($result && $adb->num_rows($result)) {
			while($resultrow = $adb->fetch_array($result)) {
				$columnnames[$resultrow['fieldname']] = array('column' => $resultrow['columnname'], 'table' => $resultrow['tablename']);
			}
		}
		return $columnnames;
	}
	
	static function detectModulenameFromRecordId($wsrecordid) {
		$adb = PearDatabase::getInstance();
		$idComponents = vtws_getIdComponents($wsrecordid);
		$result = $adb->pquery("SELECT name FROM vtiger_ws_entity WHERE id=?", array($idComponents[0]));
		if($result && $adb->num_rows($result)) {
			return $adb->query_result($result, 0, 'name');
		}
		return false;
	}
	
	static $detectFieldnamesToResolveCache = array();
	
	static function detectFieldnamesToResolve($module) {
		$adb = PearDatabase::getInstance();
		
		// Cache hit?
		if(isset(self::$detectFieldnamesToResolveCache[$module])) {
			return self::$detectFieldnamesToResolveCache[$module];
		}
		
		$resolveUITypes = array(10, 101, 116, 117, 26, 357, 50, 51, 52, 53, 57, 58, 59, 66, 68, 73, 75, 76, 77, 80, 81);
		
		$result = $adb->pquery(
			"SELECT DISTINCT fieldname FROM vtiger_field WHERE uitype IN(". 
			generateQuestionMarks($resolveUITypes) .") AND tabid=?", array($resolveUITypes, getTabid($module)) 
		);
		$fieldnames = array();
		while($resultrow = $adb->fetch_array($result)) {
			$fieldnames[] = $resultrow['fieldname'];
		}
		
		// Cache information		
		self::$detectFieldnamesToResolveCache[$module] = $fieldnames;
		
		return $fieldnames;
	}

	static $gatherModuleFieldGroupInfoCache = array();
	
	static function gatherModuleFieldGroupInfo($module) {
		$adb = PearDatabase::getInstance();
		
		if($module == 'Events') $module = 'Calendar';
		
		// Cache hit?
		if(isset(self::$gatherModuleFieldGroupInfoCache[$module])) {
			return self::$gatherModuleFieldGroupInfoCache[$module];
		}
		
		$result = $adb->pquery(
			"SELECT fieldname, fieldlabel, blocklabel, uitype FROM vtiger_field INNER JOIN
			vtiger_blocks ON vtiger_blocks.tabid=vtiger_field.tabid AND vtiger_blocks.blockid=vtiger_field.block 
			WHERE vtiger_field.tabid=? AND vtiger_field.presence != 1 ORDER BY vtiger_blocks.sequence, vtiger_field.sequence", array(getTabid($module))
		);

		$fieldgroups = array();
		while($resultrow = $adb->fetch_array($result)) {
			$blocklabel = getTranslatedString($resultrow['blocklabel'], $module);
			if(!isset($fieldgroups[$blocklabel])) {
				$fieldgroups[$blocklabel] = array();
			}
			$fieldgroups[$blocklabel][$resultrow['fieldname']] = 
				array(
					'label' => getTranslatedString($resultrow['fieldlabel'], $module),
					'uitype'=> self::fixUIType($module, $resultrow['fieldname'], $resultrow['uitype'])
				);
		}
		
		// Cache information
		self::$gatherModuleFieldGroupInfoCache[$module] = $fieldgroups;
		
		return $fieldgroups;
	}
	
	static function documentFoldersInfo() {
		$adb = PearDatabase::getInstance();
		$folders = $adb->pquery("SELECT `tree`,`name` FROM
				`vtiger_trees_templates_data` 
			INNER JOIN `vtiger_field` 
				ON `vtiger_trees_templates_data`.`templateid` = `vtiger_field`.`fieldparams` 
			WHERE `vtiger_field`.`columnname` = ? 
				AND `vtiger_field`.`tablename` = ?;", array('folderid', 'vtiger_notes'));
		$folderOptions = array();
		while( $folderrow = $adb->fetch_array($folders) ) {
			$folderwsid = sprintf("%sx%s", self::getEntityModuleWSId('DocumentFolders'), $folderrow['folderid']);
			$folderOptions[] = array( 'value' => $folderwsid, 'label' => $folderrow['foldername'] );
		} 
		return $folderOptions;
	}
	
	static function salutationValues() {
		$values = vtlib_getPicklistValues('salutationtype');
		$options = array();
		foreach($values as $value) {
			$options[] = array( 'value' => $value, 'label' => $value);
		}
		return $options;
	}
	
	static function visibilityValues() {
		$options = array();
		// Avoid translation for these picklist values.
		$options[] = array ('value' => 'Private', 'label' => 'Private');
		$options[] = array ('value' => 'Public', 'label' => 'Public');		
		return $options;
	}
	
	static function fixUIType($module, $fieldname, $uitype) {
		if ($module == 'Contacts' || $module == 'Leads') {
			if ($fieldname == 'salutationtype') {
				return 16;
			}
		}
		else if ($module == 'Calendar' || $module == 'Events') {
			if ($fieldname == 'time_start' || $fieldname == 'time_end') {
				// Special type for mandatory time type (not defined in product)
				return 252;
			}
		}
		return $uitype;
	}
	
	static function fixDescribeFieldInfo($module, &$describeInfo) {
		
		if ($module == 'Leads' || $module == 'Contacts') {
			foreach($describeInfo['fields'] as $index => $fieldInfo) {
				if ($fieldInfo['name'] == 'salutationtype') {
					$picklistValues = self::salutationValues();
					$fieldInfo['uitype'] = self::fixUIType($module, $fieldInfo['name'], $fieldInfo['uitype']) ;
					$fieldInfo['type']['name'] = 'picklist';
					$fieldInfo['type']['picklistValues'] = $picklistValues;
					//$fieldInfo['type']['defaultValue'] = $picklistValues[0];
					
					$describeInfo['fields'][$index] = $fieldInfo;
				}
			}
		}		
		else if ($module == 'Documents') {
			foreach($describeInfo['fields'] as $index => $fieldInfo) {
				if ($fieldInfo['name'] == 'folderid') {
					$picklistValues = self::documentFoldersInfo();
					$fieldInfo['type']['picklistValues'] = $picklistValues;
					//$fieldInfo['type']['defaultValue'] = $picklistValues[0];
					
					$describeInfo['fields'][$index] = $fieldInfo;
				}
			}
		} 
		else if($module == 'Calendar' || $module == 'Events') {
			foreach($describeInfo['fields'] as $index => $fieldInfo) {
				$fieldInfo['uitype'] = self::fixUIType($module, $fieldInfo['name'], $fieldInfo['uitype']); 				
				if ($fieldInfo['name'] == 'activitytype') {
					// Provide the option to create Todo like anyother Event.
					$taskTypeFound = false;
					foreach ($fieldInfo['type']['picklistValues'] as $option) {
						if ($option['value'] == 'Task') { $taskTypeFound = true; break; }
					}
					if (!$taskTypeFound) {
						array_unshift($fieldInfo['type']['picklistValues'], array('label' => 'Task', 'value' => 'Task'));
					}
				} else if ($fieldInfo['name'] == 'visibility') {
					if (empty($fieldInfo['type']['picklistValues'])) {
						$fieldInfo['type']['picklistValues'] = self::visibilityValues();
						$fieldInfo['type']['defaultValue'] = $fieldInfo['type']['picklistValues'][0]['value'];
					}
				}
				$describeInfo['fields'][$index] = $fieldInfo;				
			}
		}
	}
	
	static function getRelatedFunctionHandler($sourceModule, $targetModule) {
		$adb = PearDatabase::getInstance();
		$relationResult = $adb->pquery("SELECT name FROM vtiger_relatedlists WHERE tabid=? and related_tabid=? and presence=0", array(getTabid($sourceModule), getTabid($targetModule)));
		$functionName = false;
		if ($adb->num_rows($relationResult)) $functionName = $adb->query_result($relationResult, 0, 'name');
		return $functionName;
	}
	
	/**
	 * Security restriction (sharing privilege) query part
	 */
	static function querySecurityFromSuffix($module, $current_user) {
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');

		$querySuffix = '';
		$tabid = getTabid($module);

		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 
			&& $defaultOrgSharingPermission[$tabid] == 3) {

				$querySuffix .= " AND (vtiger_crmentity.smownerid in($current_user->id) OR vtiger_crmentity.smownerid IN 
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role 
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid 
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid 
						WHERE vtiger_role.parentrole LIKE '".$current_user_parent_role_seq."::%'
					) 
					OR vtiger_crmentity.smownerid IN 
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per 
						WHERE userid=".$current_user->id." AND tabid=".$tabid."
					) 
					OR 
						(";
		
					// Build the query based on the group association of current user.
					if(sizeof($current_user_groups) > 0) {
						$querySuffix .= " vtiger_groups.groupid IN (". implode(",", $current_user_groups) .") OR ";
					}
					$querySuffix .= " vtiger_groups.groupid IN 
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid 
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=".$current_user->id." and tabid=".$tabid."
						)";
				$querySuffix .= ")
				)";
		}
		return $querySuffix;
	}
}
