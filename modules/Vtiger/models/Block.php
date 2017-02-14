<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Block_Model extends vtlib\Block
{

	public $fields = false;

	public function getFields()
	{
		if (empty($this->fields)) {
			$moduleFields = Vtiger_Field_Model::getAllForModule($this->module);
			$this->fields = [];
			// if block does not contains any fields 
			if (!isset($moduleFields[$this->id])) {
				$moduleFields[$this->id] = [];
			}
			foreach ($moduleFields[$this->id] as &$field) {
				$this->fields[$field->get('name')] = $field;
			}
		}
		return $this->fields;
	}

	public function setFields($fieldModelList)
	{
		$this->fields = $fieldModelList;
		return $this;
	}

	/**
	 * Function to get the value of a given property
	 * @param string $propertyName
	 * @return <Object>
	 */
	public function get($propertyName)
	{
		if (property_exists($this, $propertyName)) {
			return $this->$propertyName;
		}
	}

	public function set($propertyName, $value)
	{
		if (property_exists($this, $propertyName)) {
			$this->$propertyName = $value;
		}
		return $this;
	}

	public function isCustomized()
	{
		return ($this->iscustom != 0) ? true : false;
	}

	public function __update()
	{
		$db = PearDatabase::getInstance();

		$query = 'UPDATE vtiger_blocks SET blocklabel=?,display_status=? WHERE blockid=?';
		$params = array($this->label, $this->display_status, $this->id);
		$db->pquery($query, $params);
	}

	/**
	 * Function to check whether the current block is hide
	 * @param Vtiger_Record_Model $record
	 * @param string $view
	 * @return boolean
	 */
	public function isHideBlock($record, $view)
	{
		$key = $this->get('id') . '_' . $record->getId() . '_' . $view;
		if (\App\Cache::staticHas(__METHOD__, $key)) {
			return \App\Cache::staticGet(__METHOD__, $key);
		}
		$showBlock = false;
		$query = (new \App\Db\Query())->from('vtiger_blocks_hide')->where(['enabled' => 1, 'blockid' => $this->get('id')])->andWhere(['like', 'view', $view]);
		$hideBlocks = $query->all();
		if ($hideBlocks) {
			vimport('~/modules/com_vtiger_workflow/VTJsonCondition.php');
			$conditionStrategy = new VTJsonCondition();
			foreach ($hideBlocks as $hideBlock) {
				$expr = \App\Json::decode($hideBlock['conditions']);
				if (!$record->getId() && $expr) {
					continue;
				}
				$showBlock = $conditionStrategy->evaluate($hideBlock['conditions'], $record);
			}
		}
		\App\Cache::staticSave(__METHOD__, $key, !$showBlock);
		return !$showBlock;
	}

	/**
	 * Function which indicates whether the block is shown or hidden
	 * @return : <boolean>
	 */
	public function isHidden()
	{
		if ($this->get('display_status') == '0') {
			return true;
		}
		return false;
	}

	/**
	 * Function to get the in active fields for the block
	 * @param type $raw - true to send field in model format or false to send in array format
	 * @return type - arrays
	 */
	public function getInActiveFields($raw = true)
	{
		$inActiveFields = [];
		$fields = $this->getFields();
		foreach ($fields as $fieldName => $fieldModel) {
			if (!$fieldModel->isActiveField()) {
				if ($raw) {
					$inActiveFields[$fieldName] = $fieldModel;
				} else {
					$fieldDetails = $fieldModel->getFieldInfo();
					$fieldDetails['fieldid'] = $fieldModel->getId();
					$inActiveFields[$fieldName] = $fieldDetails;
				}
			}
		}
		return $inActiveFields;
	}

	/**
	 * Function to retrieve block instances for a module
	 * @param <type> $moduleModel - module instance
	 * @return <array> - list of Vtiger_Block_Model
	 */
	public static function getAllForModule($moduleModel)
	{
		$blockObjects = Vtiger_Cache::get('ModuleBlock', $moduleModel->getName());

		if (!$blockObjects) {
			$blockObjects = parent::getAllForModule($moduleModel);
			Vtiger_Cache::set('ModuleBlock', $moduleModel->getName(), $blockObjects);
		}
		$blockModelList = [];

		if ($blockObjects) {
			foreach ($blockObjects as $blockObject) {
				$blockModelList[] = self::getInstanceFromBlockObject($blockObject);
			}
		}
		return $blockModelList;
	}

	public static function getInstance($value, $moduleInstance = false)
	{
		$blockInstance = parent::getInstance($value, $moduleInstance);
		$blockModel = self::getInstanceFromBlockObject($blockInstance);
		return $blockModel;
	}

	/**
	 * Function to retrieve block instance from vtlib\Block object
	 * @param vtlib\Block $blockObject - vtlib block object
	 * @return Vtiger_Block_Model
	 */
	public static function getInstanceFromBlockObject(vtlib\Block $blockObject)
	{
		$objectProperties = get_object_vars($blockObject);
		$blockClassName = Vtiger_Loader::getComponentClassName('Model', 'Block', $blockObject->module->name);
		$blockModel = new $blockClassName();
		foreach ($objectProperties as $properName => $propertyValue) {
			$blockModel->$properName = $propertyValue;
		}
		return $blockModel;
	}

	public static function updateSequenceNumber($sequenceList)
	{
		$db = PearDatabase::getInstance();
		$query = 'UPDATE vtiger_blocks SET sequence = CASE blockid ';
		foreach ($sequenceList as $blockId => $sequence) {
			$query .= ' WHEN ' . $blockId . ' THEN ' . $sequence;
		}
		$query .= sprintf(' END WHERE blockid IN (%s)', generateQuestionMarks($sequenceList));
		$db->pquery($query, array_keys($sequenceList));
	}

	public static function checkFieldsExists($blockId)
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT 1 FROM vtiger_field WHERE block=?';
		$result = $db->pquery($query, array($blockId));
		return ($db->num_rows($result) > 0) ? true : false;
	}

	/**
	 * Function to push all blocks down after sequence number
	 * @param type $fromSequence 
	 */
	public static function pushDown($fromSequence, $sourceModuleTabId)
	{
		$db = PearDatabase::getInstance();
		$query = 'UPDATE vtiger_blocks SET sequence=sequence+1 WHERE sequence > ? and tabid=?';
		$result = $db->pquery($query, array($fromSequence, $sourceModuleTabId));
	}

	public static function getAllBlockSequenceList($moduleTabId)
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT blockid,sequence FROM vtiger_blocks where tabid=?';
		$result = $db->pquery($query, array($moduleTabId));
		$response = [];
		$num_rows = $db->num_rows($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$response[$row['blockid']] = $row['sequence'];
		}
		return $response;
	}

	/**
	 * Function to check whether duplicate exist or not
	 * @param string $blockLabel
	 * @param number ModuleId
	 * @return boolean true/false
	 */
	public static function checkDuplicate($blockLabel, $tabId)
	{
		return (new \App\Db\Query())->from('vtiger_blocks')->where(['blocklabel' => $blockLabel, 'tabid' => $tabId])->exists();
	}
}
