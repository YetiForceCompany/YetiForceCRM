<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

require_once 'include/utils/utils.php';
require_once 'modules/PickList/PickListUtils.php';

class Vtiger_DependencyPicklist {

	static function getDependentPicklistFields($module='') {
		global $adb;

		if(empty($module)) {
			$result = $adb->pquery('SELECT DISTINCT sourcefield, targetfield, tabid FROM vtiger_picklist_dependency', array());
		} else {
			$tabId = getTabid($module);
			$result = $adb->pquery('SELECT DISTINCT sourcefield, targetfield, tabid FROM vtiger_picklist_dependency WHERE tabid=?', array($tabId));
		}
		$noofrows = $adb->num_rows($result);

		$dependentPicklists = array();
		if($noofrows > 0) {
			$fieldlist = array();
			for($i=0; $i<$noofrows; ++$i) {
				$fieldTabId = $adb->query_result($result,$i,'tabid');
				$sourceField = $adb->query_result($result,$i,'sourcefield');
				$targetField = $adb->query_result($result,$i,'targetfield');

				if(getFieldid($fieldTabId, $sourceField) == false || getFieldid($fieldTabId, $targetField) == false) {
					continue;
				}

				$fieldResult = $adb->pquery('SELECT fieldlabel FROM vtiger_field WHERE fieldname = ?', array($sourceField));
				$sourceFieldLabel = $adb->query_result($fieldResult,0,'fieldlabel');

				$fieldResult = $adb->pquery('SELECT fieldlabel FROM vtiger_field WHERE fieldname = ?', array($targetField));
				$targetFieldLabel = $adb->query_result($fieldResult,0,'fieldlabel');

				$dependentPicklists[] = array('sourcefield'=>$sourceField, 'sourcefieldlabel'=>$sourceFieldLabel,
						'targetfield'=>$targetField, 'targetfieldlabel'=>$targetFieldLabel,
						'module'=>getTabModuleName($fieldTabId));
			}
		}
		return $dependentPicklists;
	}

	static function getAvailablePicklists($module) {
		global $adb, $log;
		$tabId = getTabid($module);

		$query="select vtiger_field.fieldlabel,vtiger_field.fieldname" .
				" FROM vtiger_field inner join vtiger_picklist on vtiger_field.fieldname = vtiger_picklist.name" .
				" where displaytype=1 and vtiger_field.tabid=? and vtiger_field.uitype in ('15','16') " .
				" and vtiger_field.presence in (0,2) ORDER BY vtiger_picklist.picklistid ASC";

		$result = $adb->pquery($query, array($tabId));
		$noofrows = $adb->num_rows($result);

		$fieldlist = array();
		if($noofrows > 0) {
			for($i=0; $i<$noofrows; ++$i) {
				$fieldlist[$adb->query_result($result,$i,"fieldname")] = $adb->query_result($result,$i,"fieldlabel");
			}
		}
		return $fieldlist;
	}

	static function savePickListDependencies($module, $dependencyMap) {
		global $adb;
		$tabId = getTabid($module);
		$sourceField = $dependencyMap['sourcefield'];
		$targetField = $dependencyMap['targetfield'];

		$valueMapping = $dependencyMap['valuemapping'];
		for($i=0; $i<count($valueMapping); ++$i) {
			$mapping = $valueMapping[$i];
			$sourceValue = $mapping['sourcevalue'];
			$targetValues = $mapping['targetvalues'];
			$serializedTargetValues = Zend_Json::encode($targetValues);

			$optionalsourcefield = $mapping['optionalsourcefield'];
			$optionalsourcevalues = $mapping['optionalsourcevalues'];

			if(!empty($optionalsourcefield)) {
				$criteria = array();
				$criteria["fieldname"] = $optionalsourcefield;
				$criteria["fieldvalues"] = $optionalsourcevalues;
				$serializedCriteria = Zend_Json::encode($criteria);
			} else {
				$serializedCriteria = null;
			}
			//to handle Accent Sensitive search in MySql
			//reference Links http://dev.mysql.com/doc/refman/5.0/en/charset-convert.html , http://stackoverflow.com/questions/500826/how-to-conduct-an-accent-sensitive-search-in-mysql
			$checkForExistenceResult = $adb->pquery("SELECT id FROM vtiger_picklist_dependency WHERE tabid=? AND sourcefield=? AND targetfield=? AND sourcevalue=CAST(? AS CHAR CHARACTER SET utf8) COLLATE utf8_bin",
					array($tabId, $sourceField, $targetField, $sourceValue));
			if($adb->num_rows($checkForExistenceResult) > 0) {
				$dependencyId = $adb->query_result($checkForExistenceResult, 0, 'id');
				$adb->pquery("UPDATE vtiger_picklist_dependency SET targetvalues=?, criteria=? WHERE id=?",
						array($serializedTargetValues, $serializedCriteria, $dependencyId));
			} else {
				$adb->pquery("INSERT INTO vtiger_picklist_dependency (id, tabid, sourcefield, targetfield, sourcevalue, targetvalues, criteria)
								VALUES (?,?,?,?,?,?,?)",
						array($adb->getUniqueID('vtiger_picklist_dependency'), $tabId, $sourceField, $targetField, $sourceValue,
						$serializedTargetValues, $serializedCriteria));
			}
		}
	}

	static function deletePickListDependencies($module, $sourceField, $targetField) {
		global $adb;

		$tabId = getTabid($module);

		$adb->pquery("DELETE FROM vtiger_picklist_dependency WHERE tabid=? AND sourcefield=? AND targetfield=?",
				array($tabId, $sourceField, $targetField));
	}

	static function getPickListDependency($module, $sourceField, $targetField) {
		global $adb;

		$tabId = getTabid($module);
		$dependencyMap = array();
		$dependencyMap['sourcefield'] = $sourceField;
		$dependencyMap['targetfield'] = $targetField;

		$result = $adb->pquery('SELECT * FROM vtiger_picklist_dependency WHERE tabid=? AND sourcefield=? AND targetfield=?',
				array($tabId,$sourceField,$targetField));
		$noOfMapping = $adb->num_rows($result);

		$valueMapping = array();
		$mappedSourceValues = array();
		for($i=0; $i<$noOfMapping; ++$i) {
			$sourceValue = $adb->query_result($result, $i, 'sourcevalue');
			$targetValues = $adb->query_result($result, $i, 'targetvalues');
			$unserializedTargetValues = Zend_Json::decode(html_entity_decode($targetValues));

			$mapping = array();
			$mapping['sourcevalue'] = $sourceValue;
			$mapping['targetvalues'] = $unserializedTargetValues;

			$valueMapping[$i] = $mapping ;
		}
		$dependencyMap['valuemapping'] = $valueMapping;

		return $dependencyMap;
	}

	static function getPicklistDependencyDatasource($module) {
		global $adb;

		$tabId = getTabid($module);

		$result = $adb->pquery('SELECT * FROM vtiger_picklist_dependency WHERE tabid=?', array($tabId));
		$noofrows = $adb->num_rows($result);

		$picklistDependencyDatasource = array();
		for($i=0; $i<$noofrows; ++$i) {
			$pickArray = array();
			$sourceField = $adb->query_result($result, $i, 'sourcefield');
			$targetField = $adb->query_result($result, $i, 'targetfield');
			$sourceValue = decode_html($adb->query_result($result, $i, 'sourcevalue'));
			$targetValues = decode_html($adb->query_result($result, $i, 'targetvalues'));
			$unserializedTargetValues = Zend_Json::decode(html_entity_decode($targetValues));
			$criteria = decode_html($adb->query_result($result, $i, 'criteria'));
			$unserializedCriteria = Zend_Json::decode(html_entity_decode($criteria));

			if(!empty($unserializedCriteria) && $unserializedCriteria['fieldname'] != null) {
				$conditionValue = array(
						"condition" => array($unserializedCriteria['fieldname'] => $unserializedCriteria['fieldvalues']),
						"values" => $unserializedTargetValues
				);
				$picklistDependencyDatasource[$sourceField][$sourceValue][$targetField][] = $conditionValue;
			} else {
				$picklistDependencyDatasource[$sourceField][$sourceValue][$targetField] = $unserializedTargetValues;
			}
			if(empty($picklistDependencyDatasource[$sourceField]['__DEFAULT__'][$targetField])) {
				foreach(getAllPicklistValues($targetField) as $picklistValue) {
					$pickArray[] = decode_html($picklistValue);
				}
				$picklistDependencyDatasource[$sourceField]['__DEFAULT__'][$targetField] = $pickArray;
			}
		}
		return $picklistDependencyDatasource;
	}

	static function getJSPicklistDependencyDatasource($module) {
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($module);
		return Zend_Json::encode($picklistDependencyDatasource);
	}

	static function checkCyclicDependency($module, $sourceField, $targetField) {
		$adb = PearDatabase::getInstance();

		// If another parent field exists for the same target field - 2 parent fields should not be allowed for a target field
		$result = $adb->pquery('SELECT 1 FROM vtiger_picklist_dependency
									WHERE tabid = ? AND targetfield = ? AND sourcefield != ?',
				array(getTabid($module), $targetField, $sourceField));
		if($adb->num_rows($result) > 0) {
			return true;
		}

		//TODO - Add required check for cyclic dependency

		return false;
	}

	static function getDependentPickListModules() {
		$adb = PearDatabase::getInstance();

		$query = 'SELECT distinct vtiger_field.tabid, vtiger_tab.tablabel, vtiger_tab.name as tabname FROM vtiger_field
						INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid
						INNER JOIN vtiger_picklist ON vtiger_picklist.name = vtiger_field.fieldname
					WHERE uitype IN (15,16)
						AND vtiger_field.tabid != 29
						AND vtiger_field.displaytype = 1
						AND vtiger_field.presence in (0,2)
					GROUP BY vtiger_field.tabid HAVING count(*) > 1';
		// END
		$result = $adb->pquery($query, array());
		while($row = $adb->fetch_array($result)) {
			$modules[$row['tablabel']] = $row['tabname'];
		}
		ksort($modules);
		return $modules;
	}

}
?>