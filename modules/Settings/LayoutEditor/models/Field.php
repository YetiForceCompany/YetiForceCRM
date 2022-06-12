<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_LayoutEditor_Field_Model extends Vtiger_Field_Model
{
	const WEBSERVICE_APPS_VISIBILITY = [
		0 => 'LBL_WSA_VISIBILITY_DEFAULT',
		1 => 'LBL_DISPLAY_TYPE_1',
		2 => 'LBL_DISPLAY_TYPE_2',
		3 => 'LBL_DISPLAY_TYPE_3',
		4 => 'LBL_DISPLAY_TYPE_4',
		9 => 'LBL_DISPLAY_TYPE_9',
		10 => 'LBL_DISPLAY_TYPE_10',
		6 => 'LBL_DISPLAY_TYPE_6',
	];

	/** @var array Translations of field types */
	public static $fieldTypeLabel = [
		1 => 'Text',
		2 => 'Text',
		5 => 'Date',
		6 => 'DateTime',
		7 => 'Integer',
		9 => 'Percent',
		10 => 'Related1M',
		11 => 'Phone',
		13 => 'Email',
		14 => 'Time',
		15 => 'Picklist',
		16 => 'Picklist',
		17 => 'URL',
		19 => 'TextArea',
		21 => 'TextArea',
		23 => 'Date',
		30 => 'Integer',
		32 => 'LBL_LANGUAGE',
		33 => 'MultiSelectCombo',
		35 => 'Country',
		51 => 'Related1M',
		52 => 'LBL_USER',
		56 => 'Checkbox',
		57 => 'Related1M',
		63 => 'Integer',
		69 => 'Image',
		70 => 'DateTime',
		71 => 'Currency',
		79 => 'DateTime',
		85 => 'Skype',
		255 => 'Text',
		300 => 'Editor',
		302 => 'Tree',
		305 => 'MultiReferenceValue',
		308 => 'RangeTime',
		309 => 'CategoryMultipicklist',
		311 => 'MultiImage',
		313 => 'Twitter',
		314 => 'MultiEmail',
		316 => 'Smtp',
		318 => 'ServerAccess',
		319 => 'MultiDomain',
		324 => 'Token',
		330 => 'MultiAttachment',
		365 => 'AdvPercentage',
	];

	/** @var array Webservice field data */
	protected $webserviceData;

	/**
	 * Function to remove field.
	 */
	public function delete()
	{
		$db = \App\Db::getInstance();
		try {
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
				'cvid' => (new \App\Db\Query())->select(['cvid'])->from('vtiger_customview')->where(['entitytype' => $fldModule]),
			])->execute();
			$db->createCommand()->delete('u_#__cv_condition', ['field_name' => $fieldname, 'module_name' => $fldModule])->execute();
			//Deleting from convert lead mapping vtiger_table- Jaguar
			if ('Leads' === $fldModule) {
				$db->createCommand()->delete('vtiger_convertleadmapping', ['leadfid' => $id])->execute();
			} elseif ('Accounts' === $fldModule) {
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
			}

			$entityInfo = \App\Module::getEntityInfo($fldModule);
			foreach (['fieldnameArr' => 'fieldname', 'searchcolumnArr' => 'searchcolumn'] as $key => $name) {
				if (false !== ($fieldNameKey = array_search($fieldname, $entityInfo[$key]))) {
					unset($entityInfo[$key][$fieldNameKey]);
					$params = [
						'name' => $name,
						'tabid' => $tabId,
						'value' => $entityInfo[$key],
					];
					Settings_Search_Module_Model::save($params);
				}
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
	public function isMandatoryOptionDisabled(): bool
	{
		$focus = $this->getModule()->getEntityInstance();
		$compulsoryMandatoryFieldList = $focus->mandatory_fields ?? [];

		return \in_array($this->getName(), $compulsoryMandatoryFieldList) || \in_array($this->get('uitype'), ['4', '70']);
	}

	/**
	 * Function which will specify whether the active option is disabled.
	 *
	 * @return bool
	 */
	public function isActiveOptionDisabled(): bool
	{
		return 0 === (int) $this->get('presence') || 306 === (int) $this->get('uitype') || $this->isMandatoryOptionDisabled();
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
	public function isDefaultValueOptionDisabled(): bool
	{
		if ($this->isMandatoryOptionDisabled() || $this->isReferenceField() || 'image' === $this->getFieldDataType() || 'multiImage' === $this->getFieldDataType()) {
			return true;
		}
		return false;
	}

	/**
	 * A function that will determine if the default value option is disabled for an WebserviceApps configuration.
	 *
	 * @return bool
	 */
	public function isDefaultValueForWebservice(): bool
	{
		return !(\in_array($this->get('uitype'), ['4', '70']) || 'image' === $this->getFieldDataType() || 'multiImage' === $this->getFieldDataType());
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
	public function isEditable(): bool
	{
		if ('ModComments' === $this->getModuleName() && \in_array($this->getName(), ['commentcontent', 'userid', 'created_user_id', 'customer', 'reasontoedit', 'parents', 'assigned_user_id', 'creator', 'modifiedtime', 'related_to', 'createdtime', 'parent_comments'])) {
			return false;
		}
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

	/** {@inheritdoc} */
	public function getFieldInfo(): array
	{
		$fieldInfo = parent::getFieldInfo();
		$fieldInfo['isQuickCreateDisabled'] = $this->isQuickCreateOptionDisabled();
		$fieldInfo['isSummaryField'] = $this->isSummaryField();
		$fieldInfo['isSummaryFieldDisabled'] = $this->isSummaryFieldOptionDisabled();
		$fieldInfo['isMassEditDisabled'] = $this->isMassEditOptionDisabled();
		$fieldInfo['isDefaultValueDisabled'] = $this->isDefaultValueOptionDisabled();
		return $fieldInfo;
	}

	/**
	 * Get webservice data.
	 *
	 * @param int $webserviceApp
	 *
	 * @return array
	 */
	public function getWebserviceData(int $webserviceApp): array
	{
		if (isset($this->webserviceData)) {
			return $this->webserviceData;
		}
		return $this->webserviceData = (new \App\Db\Query())->from('w_#__fields_server')->where(['fieldid' => $this->getId(), 'serverid' => $webserviceApp])->one(\App\Db::getInstance('webservice')) ?: [];
	}

	/**
	 * Load webservice data.
	 *
	 * @param int $webserviceApp
	 *
	 * @return void
	 */
	public function loadWebserviceData(int $webserviceApp): void
	{
		$data = $this->getWebserviceData($webserviceApp);
		if (empty($data['is_default'])) {
			$this->set('defaultvalue', '');
		} else {
			$this->set('defaultvalue', $data['default_value']);
		}
		if (!empty($data['visibility'])) {
			$this->set('displaytype', $data['visibility']);
		}
	}

	/**
	 * Update webservice data.
	 *
	 * @param array $data
	 * @param int   $webserviceApp
	 *
	 * @return void
	 */
	public function updateWebserviceData(array $data, int $webserviceApp): void
	{
		$createCommand = \App\Db::getInstance('webservice')->createCommand();
		if ($this->getWebserviceData($webserviceApp)) {
			$createCommand->update('w_#__fields_server', $data, ['fieldid' => $this->getId(), 'serverid' => $webserviceApp])->execute();
		} else {
			$createCommand->insert('w_#__fields_server', \App\Utils::merge($data, ['fieldid' => $this->getId(), 'serverid' => $webserviceApp]))->execute();
		}
		\App\Cache::delete('WebserviceAppsFields', $webserviceApp);
	}
}
