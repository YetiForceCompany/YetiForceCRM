<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Settings_LayoutEditor_Field_Model extends Vtiger_Field_Model
{
	/**
	 * Function to remove field.
	 */
	public function delete()
	{
		$db = \App\Db::getInstance();
		try {
			$uiType = $this->getUIType();
			if (10 === $uiType) {
				$reference = $this->getReferenceList();
			}
			parent::delete();

			$fldModule = $this->getModuleName();
			$id = $this->getId();
			$fieldname = $this->getName();
			$tablename = $this->get('table');
			$columnName = $this->get('column');
			$tabId = $this->getModuleId();
			if ('vtiger_crmentity' !== $tablename) {
				$db->createCommand()->dropColumn($tablename, $columnName)->execute();
			}
			App\Db::getInstance('admin')->createCommand()->delete('a_#__mapped_fields', ['or', ['source' => (string) $id], ['target' => (string) $id]])->execute();
			//we have to remove the entries in customview and report related tables which have this field ($colName)
			$db->createCommand()->delete('vtiger_cvcolumnlist', ['field_name' => $fieldname, 'module_name' => $fldModule])->execute();
			$db->createCommand()->delete('vtiger_cvcolumnlist', [
				'source_field_name' => $fieldname,
				'cvid' => (new \App\Db\Query())->select(['cvid'])->from('vtiger_customview')->where(['entitytype' => $fldModule])
			])->execute();
			$db->createCommand()->delete('u_#__cv_condition', ['field_name' => $fieldname, 'module_name' => $fldModule])->execute();
			//Deleting from convert lead mapping vtiger_table- Jaguar
			if ('Leads' === $fldModule) {
				$db->createCommand()->delete('vtiger_convertleadmapping', ['leadfid' => $id])->execute();
			} elseif ('Accounts' == $fldModule) {
				$mapDelId = ['Accounts' => 'accountfid'];
				$db->createCommand()->update('vtiger_convertleadmapping', [$mapDelId[$fldModule] => 0], [$mapDelId[$fldModule] => $id])->execute();
			}

			//HANDLE HERE - we have to remove the table for other picklist type values which are text area and multiselect combo box
			if ('picklist' === $this->getFieldDataType() || 'multipicklist' === $this->getFieldDataType()) {
				$query = (new \App\Db\Query())->from('vtiger_field')
					->where(['fieldname' => $fieldname])
					->andWhere(['in', 'uitype', [15, 16, 33]]);
				$dataReader = $query->createCommand()->query();
				if (!$dataReader->count()) {
					$db->createCommand()->dropTable('vtiger_' . $fieldname)->execute();
					//To Delete Sequence Table
					if ($db->isTableExists('vtiger_' . $fieldname . '_seq')) {
						$db->createCommand()->dropTable('vtiger_' . $fieldname . '_seq')->execute();
					}
					$db->createCommand()->delete('vtiger_picklist', ['name' => $fieldname])->execute();
				}
				$db->createCommand()->delete('vtiger_picklist_dependency', ['and', ['tabid' => $tabId], ['or', ['sourcefield' => $fieldname], ['targetfield' => $fieldname]]])->execute();
			}

			if (305 === $uiType) {
				$fieldParams = \App\Json::decode($this->get('fieldparams'));
				$destModule = $fieldParams['module'];
				$db->createCommand()->delete('s_#__multireference', ['source_module' => $fldModule, 'dest_module' => $destModule])->execute();
				\App\Cache::delete('mrvfbm', "{$fldModule},{$destModule}");
				\App\Cache::delete('getMultiReferenceModules', $destModule);
			}
			$tabIds = (new \App\Db\Query())
				->select(['fieldid', 'tabid'])
				->from('vtiger_field')
				->where(['and',	['<>', 'presence', 1], ['uitype' => 305],	['and', ['like', 'fieldparams', '"field":"' . $id . '"']]
				])->createCommand()->queryAllByGroup();
			foreach ($tabIds as $fieldId => $tabId) {
				$sourceModule = \App\Module::getModuleName($tabId);
				$db->createCommand()->update('vtiger_field', ['presence' => 1], ['fieldid' => $fieldId])->execute();
				\App\Cache::delete('mrvfbm', "{$sourceModule},{$fldModule}");
				\App\Cache::delete('getMultiReferenceModules', $fldModule);
			}

			if (10 === $uiType && $reference) {
				$db->createCommand()->delete('vtiger_relatedlists', ['field_name' => $fieldname, 'related_tabid' => $tabId, 'tabid' => array_map('App\Module::getModuleId', $reference)])->execute();
				foreach ($reference as $module) {
					\App\Relation::clearCacheByModule($module);
				}
				\App\Cache::delete('HierarchyByRelation', '');
			}

			$entityInfo = \App\Module::getEntityInfo($fldModule);
			foreach (['fieldnameArr' => 'fieldname', 'searchcolumnArr' => 'searchcolumn'] as $key => $name) {
				if (false !== ($fieldNameKey = array_search($fieldname, $entityInfo[$key]))) {
					unset($entityInfo[$key][$fieldNameKey]);
					$params = [
						'name' => $name,
						'tabid' => $tabId,
						'value' => $entityInfo[$key]
					];
					Settings_Search_Module_Model::save($params);
				}
			}
			if (11 === $uiType && ($extraFieldId = (new \App\Db\Query())->select(['fieldid'])->from('vtiger_field')->where(['fieldname' => "{$fieldname}_extra", 'tabid' => $tabId])->scalar())) {
				self::getInstance($extraFieldId)->delete();
			}
		} catch (\Throwable $ex) {
			\App\Log::error($ex->__toString());
			throw $ex;
		}
	}

	/**
	 * Function to Move the field.
	 *
	 * @param <Array> $fieldNewDetails
	 * @param <Array> $fieldOlderDetails
	 */
	public function move($fieldNewDetails, $fieldOlderDetails)
	{
		$db = \App\Db::getInstance();
		$newBlockId = $fieldNewDetails['blockId'];
		$olderBlockId = $fieldOlderDetails['blockId'];

		$newSequence = $fieldNewDetails['sequence'];
		$olderSequence = $fieldOlderDetails['sequence'];

		if ($olderBlockId == $newBlockId) {
			if ($newSequence > $olderSequence) {
				$db->createCommand()->update('vtiger_field', ['sequence' => new \yii\db\Expression('sequence - 1')], ['and', 'sequence > :olderSequence', 'sequence <= :newSequence', 'block = :olderBlockId'], [':olderSequence' => $olderSequence, ':newSequence' => $newSequence, ':olderBlockId' => $olderBlockId])->execute();
			} elseif ($newSequence < $olderSequence) {
				$db->createCommand()->update('vtiger_field', ['sequence' => new \yii\db\Expression('sequence + 1')], ['and', 'sequence < :olderSequence', 'sequence >= :newSequence', 'block = :olderBlockId'], [':olderSequence' => $olderSequence, ':newSequence' => $newSequence, ':olderBlockId' => $olderBlockId])->execute();
			}
			$db->createCommand()->update('vtiger_field', ['sequence' => $newSequence], ['fieldid' => $this->getId()])->execute();
		} else {
			$db->createCommand()->update('vtiger_field', ['sequence' => new \yii\db\Expression('sequence - 1')], ['and', 'sequence > :olderSequence', 'block = :olderBlockId'], [':olderSequence' => $olderSequence, ':olderBlockId' => $olderBlockId])->execute();
			$db->createCommand()->update('vtiger_field', ['sequence' => new \yii\db\Expression('sequence - 1')], ['and', 'sequence >= :newSequence', 'block = :newBlockId'], [':newSequence' => $newSequence, ':newBlockId' => $newBlockId])->execute();

			$db->createCommand()->update('vtiger_field', ['sequence' => $newSequence, 'block' => $newBlockId], ['fieldid' => $this->getId()])->execute();
		}
	}

	/**
	 * Function to activate field.
	 *
	 * @param int[] $fieldIdsList
	 * @param int   $blockId
	 */
	public static function makeFieldActive($fieldIdsList, $blockId)
	{
		$maxSequence = (new \App\Db\Query())->from('vtiger_field')->where(['block' => $blockId, 'presence' => [0, 2]])->max('sequence');
		$db = \App\Db::getInstance();
		$caseExpression = 'CASE';
		foreach ($fieldIdsList as $fieldId) {
			$caseExpression .= " WHEN fieldid = {$db->quoteValue($fieldId)} THEN {$db->quoteValue($maxSequence + 1)}";
		}
		$caseExpression .= ' ELSE sequence END';
		$db->createCommand()
			->update('vtiger_field', [
				'presence' => 2,
				'sequence' => new \yii\db\Expression($caseExpression),
			], ['fieldid' => $fieldIdsList])->execute();
		\App\Cache::clear();
		\App\Colors::generate('picklist');
	}

	/**
	 * Function which specifies whether the field can have mandatory switch to happen.
	 *
	 * @return bool - true if we can make a field mandatory and non mandatory , false if we cant change previous state
	 */
	public function isMandatoryOptionDisabled()
	{
		$moduleModel = $this->getModule();
		$complusoryMandatoryFieldList = $moduleModel->getCumplosoryMandatoryFieldList();
		//uitypes for which mandatory switch is disabled
		$mandatoryRestrictedUitypes = ['4', '70'];
		if (\in_array($this->getName(), $complusoryMandatoryFieldList)) {
			return true;
		}
		if (\in_array($this->get('uitype'), $mandatoryRestrictedUitypes)) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the active option is disabled.
	 *
	 * @return bool
	 */
	public function isActiveOptionDisabled()
	{
		if (0 == $this->get('presence') || 306 == $this->get('uitype') || $this->isMandatoryOptionDisabled()) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the quickcreate option is disabled.
	 *
	 * @return bool
	 */
	public function isQuickCreateOptionDisabled()
	{
		$moduleModel = $this->getModule();
		if (0 == $this->get('quickcreate') || 3 == $this->get('quickcreate') || !$moduleModel->isQuickCreateSupported()) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the mass edit option is disabled.
	 *
	 * @return bool
	 */
	public function isMassEditOptionDisabled()
	{
		if (0 == $this->get('masseditable') || 1 != $this->get('displaytype') || 3 == $this->get('masseditable')) {
			return true;
		}
		return false;
	}

	/**
	 * Function which will specify whether the default value option is disabled.
	 *
	 * @return bool
	 */
	public function isDefaultValueOptionDisabled()
	{
		if ($this->isMandatoryOptionDisabled() || $this->isReferenceField() || 'image' === $this->getFieldDataType() || 'multiImage' === $this->getFieldDataType()) {
			return true;
		}
		return false;
	}

	/**
	 * Function to check whether summary field option is disable or not.
	 *
	 * @return bool true/false
	 */
	public function isSummaryFieldOptionDisabled()
	{
		return 70 === $this->get('uitype');
	}

	/**
	 * Function to check field is editable or not.
	 *
	 * @return bool true/false
	 */
	public function isEditable()
	{
		return true;
	}

	/**
	 * Function to get instance.
	 *
	 * @param string $value  - fieldname or fieldid
	 * @param <type> $module - optional - module instance
	 *
	 * @return self
	 */
	public static function getInstance($value, $module = false)
	{
		$fieldObject = parent::getInstance($value, $module);
		$objectProperties = get_object_vars($fieldObject);
		$fieldModel = new self();
		foreach ($objectProperties as $properName => $propertyValue) {
			$fieldModel->{$properName} = $propertyValue;
		}
		return $fieldModel;
	}

	/**
	 * Function to get all fields list for all blocks.
	 *
	 * @param array List of block ids
	 * @param Vtiger_Module_Model $moduleInstance
	 * @param mixed               $blockId
	 *
	 * @return array List of Field models Settings_LayoutEditor_Field_Model
	 */
	public static function getInstanceFromBlockIdList($blockId, $moduleInstance = false)
	{
		if (!\is_array($blockId)) {
			$blockId = [$blockId];
		}
		$query = (new \App\Db\Query())->from('vtiger_field')->where(['block' => $blockId])->orderBy('sequence');
		$dataReader = $query->createCommand()->query();
		$fieldModelsList = [];
		while ($row = $dataReader->read()) {
			$fieldModel = new self();
			$fieldModel->initialize($row);
			if ($moduleInstance) {
				$fieldModel->setModule($moduleInstance);
			}
			$fieldModelsList[$row['fieldname']] = $fieldModel;
		}
		$dataReader->close();

		return $fieldModelsList;
	}

	/**
	 * Function to get the field details.
	 *
	 * @return <Array> - array of field values
	 */
	public function getFieldInfo()
	{
		$fieldInfo = parent::getFieldInfo();
		$fieldInfo['isQuickCreateDisabled'] = $this->isQuickCreateOptionDisabled();
		$fieldInfo['isSummaryField'] = $this->isSummaryField();
		$fieldInfo['isSummaryFieldDisabled'] = $this->isSummaryFieldOptionDisabled();
		$fieldInfo['isMassEditDisabled'] = $this->isMassEditOptionDisabled();
		$fieldInfo['isDefaultValueDisabled'] = $this->isDefaultValueOptionDisabled();
		return $fieldInfo;
	}
}
