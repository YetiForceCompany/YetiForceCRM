<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/libraries/freetag/freetag.class.php');

class Vtiger_Tag_Model extends Vtiger_Base_Model {

	private $_freetag = false;

	static $TAG_FETCH_LIMIT = 100;

	function __construct() {
		$this->_freetag = new freetag();
	}

	/**
	 * Function saves a tag to database
	 */
	public function save() {
		$this->_freetag->tag_object($this->get('userid'), $this->get('record'), $this->get('tagname'), $this->get('module'));
	}

	/**
	 * Function deletes a tag from database
	 */
	public function delete() {
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE FROM vtiger_freetagged_objects WHERE tag_id = ? AND object_id = ?',
				array($this->get('tag_id'), $this->get('record')));
	}

	/**
	 * Function returns the tags
	 * @param type $userId
	 * @param type $module
	 * @param type $record
	 * @return type
	 */
	public static function getAll($userId = NULL, $module = "", $record = NULL) {
		$tag = new self();
		return $tag->_freetag->get_tag_cloud_tags(self::$TAG_FETCH_LIMIT, $userId, $module, $record);
	}
	
	public static function getTaggedRecords($tagId) {
		$recordModels = array();
		if(!empty($tagId)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT vtiger_crmentity.* FROM vtiger_freetags 
				INNER JOIN vtiger_freetagged_objects ON vtiger_freetags.id = vtiger_freetagged_objects.tag_id 
				INNER JOIN vtiger_crmentity ON vtiger_freetagged_objects.object_id=vtiger_crmentity.crmid 
					AND vtiger_crmentity.deleted=0 
				WHERE tag_id = ?", array($tagId));
			$rows = $db->num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$row = $db->query_result_rowdata($result, $i);
				$recordModel = Vtiger_Record_Model::getCleanInstance($row['setype']);
				$recordModel->setData($row);
				$recordModel->setId($row['crmid']);
				$recordModels[$row['setype']][] = $recordModel;
			}
		}
		return $recordModels;
	}
}

?>
