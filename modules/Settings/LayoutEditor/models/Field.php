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

class Settings_LayoutEditor_Field_Model extends Settings_Vtiger_Field_Model
{
	/** @var Settings_Vtiger_Field_Model[] Item field models */
	private $items = [];
	/** @var Vtiger_Field_Model|null Source field model */
	public $sourceFieldModel;
	/** @var array Webservice app visibility */
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
	public $fieldTypeLabel = [
		'string' => 'Text',
		'date' => 'Date',
		'integer' => 'Integer',
		'double' => 'Decimal',
		'percentage' => 'Percent',
		'phone' => 'Phone',
		'email' => 'Email',
		'time' => 'Time',
		'picklist' => 'Picklist',
		'url' => 'URL',
		'multipicklistTags' => 'MultipicklistTags',
		'text' => 'TextArea',
		'languages' => 'LBL_LANGUAGE',
		'multipicklist' => 'MultiSelectCombo',
		'country' => 'Country',
		'reference' => 'Related1M',
		'userCreator' => 'LBL_USER',
		'boolean' => 'Checkbox',
		'image' => 'Image',
		'datetime' => 'DateTime',
		'currency' => 'Currency',
		'skype' => 'Skype',
		'tree' => 'Tree',
		'multiReferenceValue' => 'MultiReferenceValue',
		'multiReference' => 'MultiReference',
		'rangeTime' => 'RangeTime',
		'categoryMultipicklist' => 'CategoryMultipicklist',
		'multiImage' => 'MultiImage',
		'twitter' => 'Twitter',
		'multiEmail' => 'MultiEmail',
		'smtp' => 'Smtp',
		'serverAccess' => 'ServerAccess',
		'multiDomain' => 'MultiDomain',
		'token' => 'Token',
		'multiAttachment' => 'MultiAttachment',
		'mapCoordinates' => 'MapCoordinates',
		'advPercentage' => 'AdvPercentage',
		'group' => 'Group'
	];

	/** @var array Webservice field data */
	protected $webserviceData;

	/**
	 * Get field data type label.
	 *
	 * @return string
	 */
	public function getFieldDataTypeLabel(): string
	{
		if (300 === $this->getUIType()) {
			$label = 'Editor';
		} else {
			$label = $this->fieldTypeLabel[$this->getFieldDataType()] ?? '';
		}
		return $label;
	}

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
			switch ($this->getFieldDataType()) {
				case 'picklist':
				case 'multipicklist':
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
					break;
					case 'mapCoordinates':
						\App\Fields\MapCoordinates::reloadHandler();
						break;
				default:
					break;
			}
			$entityInfo = \App\Module::getEntityInfo($fldModule);
			$searchModel = Settings_Search_Module_Model::getInstance('Settings:Search');
			foreach (['fieldnameArr' => 'fieldname', 'searchcolumnArr' => 'searchcolumn'] as $key => $name) {
				if (false !== ($fieldNameKey = array_search($fieldname, $entityInfo[$key]))) {
					unset($entityInfo[$key][$fieldNameKey]);
					$params = [
						'name' => $name,
						'tabid' => $tabId,
						'value' => $entityInfo[$key],
					];
					$searchModel->save($params);
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
		$maxSequence = (new \App\Db\Query())->from('vtiger_field')
			->where(['block' => $blockId, 'presence' => [0, 2]])->max('sequence');
		foreach ($fieldIdsList as $fieldId) {
			++$maxSequence;
			$fieldInstance = self::getInstance($fieldId);
			$fieldInstance->set('sequence', $maxSequence);
			$fieldInstance->set('presence', 2);
			$fieldInstance->save();
		}
	}

	/**
	 * Set source field model.
	 *
	 * @param Vtiger_Field_Model $fieldModel
	 *
	 * @return $this
	 */
	public function setSourceField(Vtiger_Field_Model $fieldModel)
	{
		$this->sourceFieldModel = $fieldModel;

		return $this;
	}

	/**
	 * Get source field model.
	 *
	 * @return Vtiger_Field_Model|null
	 */
	public function getSourceField(): ?Vtiger_Field_Model
	{
		return $this->sourceFieldModel;
	}

	/**
	 * Get module model.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getModule()
	{
		return $this->getSourceField() ? $this->getSourceField()->getModule() : parent::getModule();
	}

	/**
	 * Function which specifies whether the field can have mandatory switch to happen.
	 *
	 * @return bool - true if we can make a field mandatory and non mandatory , false if we cant change previous state
	 */
	public function isMandatoryOptionDisabled(): bool
	{
		$compulsoryMandatoryFieldList = [];
		if (!$this->getSourceField()) {
			$compulsoryMandatoryFieldList = $this->getModule()->getEntityInstance()->mandatory_fields ?? [];
		}

		return \in_array($this->getName(), $compulsoryMandatoryFieldList) || \in_array($this->get('uitype'), ['4', '70']);
	}

	/**
	 * Function which will specify whether the active option is disabled.
	 *
	 * @return bool
	 */
	public function isActiveOptionDisabled(): bool
	{
		if (!($sourceField = $this->getSourceField())) {
			$sourceField = $this;
		}

		return 0 === (int) $sourceField->get('presence') || 306 === (int) $sourceField->get('uitype') || $this->isMandatoryOptionDisabled();
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
	 * @param string $viewName
	 *
	 * @return bool true/false
	 */
	public function isEditable(string $viewName = 'Edit'): bool
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

	/**
	 * Get fields instance by name.
	 *
	 * @param string $name
	 *
	 * @return Vtiger_Field_Model
	 */
	public function getFieldItemByName($name)
	{
		if (isset($this->items[$name])) {
			return $this->items[$name];
		}
		$params = [];
		$itemModel = null;
		$qualifiedModuleName = 'Settings:LayoutEditor';
		switch ($name) {
			case 'icon':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_ICON',
					'uitype' => 62,
					'typeofdata' => 'V~O',
					'maximumlength' => '255',
					'purifyType' => \App\Purifier::TEXT,
					'table' => 'vtiger_field',
					'fieldDataType' => 'icon'
				];
				break;
			case 'fieldlabel':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_LABEL',
					'uitype' => 1,
					'typeofdata' => 'V~M',
					'maximumlength' => '50',
					'purifyType' => \App\Purifier::TEXT
				];
				break;
			case 'mandatory':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_MANDATORY_FIELD',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL
				];
				break;
			case 'presence':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_ACTIVE',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL
				];
				break;
			case 'quickcreate':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_QUICK_CREATE',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL
				];
				break;
			case 'summaryfield':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_SUMMARY_FIELD',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL
				];
				break;
			case 'header_field':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_HEADER_FIELD',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL
				];
				break;
			case 'masseditable':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_MASS_EDIT',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL
				];
				break;
			case 'generatedtype':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_GENERATED_TYPE',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL,
					'isEditableReadOnly' => !App\Config::developer('CHANGE_GENERATEDTYPE')
				];
				break;
			case 'defaultvalue':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_DEFAULT_VALUE',
					'uitype' => 56,
					'typeofdata' => 'C~O',
					'maximumlength' => '1',
					'purifyType' => \App\Purifier::BOOL
				];
				break;
			case 'fieldMask':
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_FIELD_MASK',
					'uitype' => 1,
					'typeofdata' => 'V~O',
					'maximumlength' => '25',
					'purifyType' => \App\Purifier::TEXT,
					'tooltip' => 'LBL_FIELD_MASK_INFO'
				];
				break;
			default:
				break;
		}
		if ($params) {
			$itemModel = \Vtiger_Field_Model::init($qualifiedModuleName, $params, $name)->setSourceField($this);
			if (null !== $this->get($name)) {
				$itemModel->set('fieldvalue', $this->get($name));
			} elseif (($defaultValue = $itemModel->get('defaultvalue')) !== null) {
				$itemModel->set('fieldvalue', $defaultValue);
			}
		}

		return $itemModel;
	}
}
