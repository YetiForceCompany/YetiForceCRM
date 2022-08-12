<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Vtiger_Block_Model extends vtlib\Block
{
	public $fields = false;

	/**
	 * Get fields.
	 *
	 * @return Vtiger_Field_Model[]|false
	 */
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
	 * Function to get the value of a given property.
	 *
	 * @param string $propertyName
	 *
	 * @return <Object>
	 */
	public function get($propertyName)
	{
		if (property_exists($this, $propertyName)) {
			return $this->{$propertyName};
		}
	}

	public function set($propertyName, $value)
	{
		if (property_exists($this, $propertyName)) {
			$this->{$propertyName} = $value;
		}
		return $this;
	}

	/**
	 * Check if block is customizable.
	 *
	 * @return bool
	 */
	public function isCustomized()
	{
		return 0 !== (int) $this->iscustom;
	}

	/**
	 * Update block.
	 */
	public function __update()
	{
		App\Db::getInstance()->createCommand()
			->update('vtiger_blocks', ['blocklabel' => $this->label, 'display_status' => $this->display_status], ['blockid' => $this->id])
			->execute();
	}

	/**
	 * Function which indicates whether the block is shown or hidden.
	 *
	 * @return bool
	 */
	public function isHidden()
	{
		if (0 === (int) $this->get('display_status')) {
			return true;
		}
		return false;
	}

	/**
	 * Function which indicates whether the block is dynamic show.
	 *
	 * @return bool
	 */
	public function isDynamic()
	{
		if (2 === (int) $this->get('display_status')) {
			return true;
		}
		return false;
	}

	/**
	 * Function to get the in active fields for the block.
	 *
	 * @param type $raw - true to send field in model format or false to send in array format
	 *
	 * @return type - arrays
	 */
	public function getInActiveFields($raw = true)
	{
		$inActiveFields = [];
		foreach ($this->getFields() as $fieldName => $fieldModel) {
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
	 * Function to retrieve block instances for a module.
	 *
	 * @param vtlib\ModuleBasic $moduleModel - module instance
	 *
	 * @return Vtiger_Block_Model[] List of Vtiger_Block_Model
	 */
	public static function getAllForModule(vtlib\ModuleBasic $moduleModel)
	{
		$blockModelList = [];
		if ($blockObjects = parent::getAllForModule($moduleModel)) {
			foreach ($blockObjects as $blockObject) {
				$blockModelList[] = self::getInstanceFromBlockObject($blockObject);
			}
		}
		return $blockModelList;
	}

	/**
	 * Get block instance.
	 *
	 * @param int|string             $value
	 * @param vtlib\ModuleBasic|bool $moduleInstance
	 *
	 * @return self
	 */
	public static function getInstance($value, $moduleInstance = false)
	{
		return self::getInstanceFromBlockObject(parent::getInstance($value, $moduleInstance));
	}

	/**
	 * Function to retrieve block instance from vtlib\Block object.
	 *
	 * @param vtlib\Block $blockObject - vtlib block object
	 *
	 * @return Vtiger_Block_Model
	 */
	public static function getInstanceFromBlockObject(vtlib\Block $blockObject)
	{
		$objectProperties = get_object_vars($blockObject);
		$blockClassName = Vtiger_Loader::getComponentClassName('Model', 'Block', $blockObject->module->name);
		$blockModel = new $blockClassName();
		foreach ($objectProperties as $properName => $propertyValue) {
			$blockModel->{$properName} = $propertyValue;
		}
		return $blockModel;
	}

	/**
	 * Update sequence number of blocks.
	 *
	 * @param int[] $sequenceList
	 */
	public static function updateSequenceNumber($sequenceList)
	{
		$db = App\Db::getInstance();
		$case = ' CASE blockid ';
		foreach ($sequenceList as $blockId => $sequence) {
			$case .= " WHEN {$db->quoteValue($blockId)} THEN {$db->quoteValue($sequence)}";
		}
		$case .= ' END';
		$db->createCommand()->update('vtiger_blocks', ['sequence' => new yii\db\Expression($case)], ['blockid' => array_keys($sequenceList)])
			->execute();
	}

	/**
	 * Check if fields are in block.
	 *
	 * @param int $blockId
	 *
	 * @return bool
	 */
	public static function checkFieldsExists($blockId)
	{
		return (new App\Db\Query())->from('vtiger_field')
			->where(['block' => $blockId])
			->exists();
	}

	/**
	 * Function to push all blocks down after sequence number.
	 *
	 * @param int $fromSequence
	 * @param int $sourceModuleTabId
	 */
	public static function pushDown($fromSequence, $sourceModuleTabId)
	{
		App\Db::getInstance()->createCommand()
			->update('vtiger_blocks', ['sequence' => new yii\db\Expression('sequence + 1')], ['and', ['>', 'sequence', $fromSequence], ['tabid' => $sourceModuleTabId]])
			->execute();
	}

	/**
	 * Function to get number sequence of blocks.
	 *
	 * @param int $moduleTabId
	 *
	 * @return array
	 */
	public static function getAllBlockSequenceList($moduleTabId)
	{
		return (new App\Db\Query())->select(['blockid', 'sequence'])
			->from('vtiger_blocks')
			->where(['tabid' => $moduleTabId])
			->createCommand()->queryAllByGroup(0);
	}

	/**
	 * Function to check whether duplicate exist or not.
	 *
	 * @param string $blockLabel
	 * @param number ModuleId
	 * @param mixed $tabId
	 *
	 * @return bool true/false
	 */
	public static function checkDuplicate($blockLabel, $tabId)
	{
		return (new \App\Db\Query())->from('vtiger_blocks')->where(['blocklabel' => $blockLabel, 'tabid' => $tabId])->exists();
	}
}
