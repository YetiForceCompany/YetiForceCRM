<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger Tracker Record Model Class
 */
class Vtiger_TrackRecord_Model extends Vtiger_Record_Model {

	/**
	 * Function to get the id of the record
	 * @return <Number> - Record Id
	 */
	public function getId() {
		return $this->get('item_id');
	}

	/**
	 * Function to get the name of the record
	 * @return <String> - Entity Name of the Record
	 */
	public function getName() {
		return $this->get('item_summary');
	}

	/**
	 * Function to get the instance of the Tracker Record Model from the list of key-value mapping
	 * @param <Array> $valueMap
	 * @return Vtiger_TrackRecord_Model instance
	 */
	public static function getInstance($valueMap) {
		$instance = new self();
		$instance->setData($valueMap);
		$instance->setModule($valueMap['module_name']);
		return $instance;
	}

	/**
	 * Function to get all the Tracker records
	 * @param <Number> $limit - Limit on the number of records
	 * @return <Array> - List of Vtiger_TrackRecord_Model instances
	 */
	public static function getAll($limit=null) {
		require_once('data/Tracker.php');
		$tracFocus = new Tracker();
		$userModel = Users_Record_Model::getCurrentUserModel();
		$list = $tracFocus->get_recently_viewed($userModel->getId());
		$trackRecords = array();
		foreach($list as $record) {
			$trackRecords[] = self::getInstance($record);
		}
		return $trackRecords;
	}
}