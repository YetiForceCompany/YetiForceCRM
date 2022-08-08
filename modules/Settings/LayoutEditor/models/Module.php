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

class Settings_LayoutEditor_Module_Model extends Vtiger_Module_Model
{
	public static $supportedModules = false;

	/**
	 * Related view types.
	 *
	 * @var string[]
	 */
	private static $relatedViewType = [
		'RelatedTab' => 'LBL_RELATED_TAB_TYPE',
		'DetailTop' => 'LBL_DETAIL_TOP_TYPE',
		'DetailBottom' => 'LBL_DETAIL_BOTTOM_TYPE',
		'SummaryTop' => 'LBL_SUMMARY_TOP_TYPE',
		'SummaryBottom' => 'LBL_SUMMARY_BOTTOM_TYPE',
	];

	/**
	 * Function that returns all the fields for the module.
	 *
	 * @param mixed $blockInstance
	 *
	 * @return Vtiger_Field_Model[] - list of field models
	 */
	public function getFields($blockInstance = false)
	{
		if (empty($this->fieldsModule)) {
			$fieldList = [];
			$blocks = $this->getBlocks();
			$blockId = [];
			foreach ($blocks as $block) {
				$blockId[] = $block->get('id');
			}
			if (\count($blockId) > 0) {
				$fieldList = Settings_LayoutEditor_Field_Model::getInstanceFromBlockIdList($blockId);
			}
			$this->fieldsModule = $fieldList;
		}
		return $this->fieldsModule;
	}

	/**
	 * Function returns all the blocks for the module.
	 *
	 * @return <Array of Vtiger_Block_Model> - list of block models
	 */
	public function getBlocks()
	{
		if (empty($this->blocks)) {
			$blocksList = [];
			$moduleBlocks = Settings_LayoutEditor_Block_Model::getAllForModule($this);
			foreach ($moduleBlocks as $block) {
				if (!$block->get('label')) {
					continue;
				}
				if ('HelpDesk' === $this->getName() && 'LBL_COMMENTS' === $block->get('label')) {
					continue;
				}

				if ('LBL_ITEM_DETAILS' != $block->get('label')) {
					$blocksList[$block->get('label')] = $block;
				}
			}
			$this->blocks = $blocksList;
		}
		return $this->blocks;
	}

	/**
	 * List of supported field types.
	 *
	 * @return string[]
	 */
	public function getAddSupportedFieldTypes()
	{
		return [
			'Text', 'Decimal', 'Integer',  'Currency',  'Percent', 'AdvPercentage', 'Date', 'Time', 'DateTime', 'RangeTime', 'Phone', 'Email', 'MultiEmail', 'MultiDomain', 'Picklist', 'MultiSelectCombo', 'Country', 'URL', 'Checkbox', 'TextArea', 'Related1M', 'MultiReference', 'Editor', 'Tree', 'CategoryMultipicklist', 'Image', 'MultiImage',  'MultiAttachment', 'MultiReferenceValue', 'ServerAccess', 'Skype', 'Twitter', 'Token', 'Smtp',
		];
	}

	/**
	 * Function which will give information about the field types that are supported for add.
	 *
	 * @return array
	 */
	public function getAddFieldTypeInfo()
	{
		$fieldTypesInfo = [];
		$addFieldSupportedTypes = $this->getAddSupportedFieldTypes();
		$lengthSupportedFieldTypes = ['Text', 'Decimal', 'Integer', 'Currency', 'Editor', 'AdvPercentage'];
		foreach ($addFieldSupportedTypes as $fieldType) {
			$details = [];
			if (\in_array($fieldType, $lengthSupportedFieldTypes)) {
				$details['lengthsupported'] = true;
			}
			if ('Editor' === $fieldType) {
				$details['noLimitForLength'] = true;
			}
			if ('Decimal' === $fieldType || 'Currency' === $fieldType || 'AdvPercentage' === $fieldType) {
				$details['decimalSupported'] = true;
				$details['maxFloatingDigits'] = 5;
				if ('Currency' === $fieldType) {
					$details['decimalReadonly'] = true;
				}
				//including mantisaa and integer part
				$details['maxLength'] = 64;
			}
			if ('Picklist' === $fieldType || 'MultiSelectCombo' === $fieldType) {
				$details['preDefinedValueExists'] = true;
				//text area value type , can give multiple values
				$details['preDefinedValueType'] = 'text';
				if ('Picklist' === $fieldType) {
					$details['picklistoption'] = true;
				}
			}
			if ('Related1M' === $fieldType) {
				$details['ModuleListMultiple'] = true;
			}
			$fieldTypesInfo[$fieldType] = $details;
		}
		return $fieldTypesInfo;
	}

	/**
	 * Verification of data.
	 *
	 * @param array $data
	 * @param bool  $throw
	 */
	public function validate(array $data, bool $throw = true)
	{
		$message = null;
		$code = null;
		$result = false;
		foreach ($data as $key => $value) {
			switch ($key) {
				case 'fieldLabel':
					if ($result = $this->checkFieldLabelExists($value)) {
						$message = \App\Language::translate('LBL_DUPLICATE_FIELD_EXISTS', 'Settings::LayoutEditor');
						$code = 513;
					}
					break;
				case 'fieldName':
					$value = strtolower($value);
					if ($result = $this->checkFieldNameCharacters($value)) {
						$message = \App\Language::translate('LBL_INVALIDCHARACTER', 'Settings::LayoutEditor');
						$code = 512;
					} elseif ($result = $this->checkFieldNameExists($value)) {
						$message = \App\Language::translate('LBL_DUPLICATE_FIELD_EXISTS', 'Settings::LayoutEditor');
						$code = 512;
					} elseif ($result = $this->checkFieldNameIsAnException($value)) {
						$message = \App\Language::translate('LBL_FIELD_NAME_IS_RESERVED', 'Settings::LayoutEditor');
						$code = 512;
					} elseif (\strlen($value) > 30) {
						$message = \App\Language::translate('LBL_EXCEEDED_MAXIMUM_NUMBER_CHARACTERS_FOR_FIELD_NAME', 'Settings::LayoutEditor');
						$code = 512;
					} elseif (isset($data['fieldType']) && \in_array($data['fieldType'], ['Picklist', 'MultiSelectCombo']) && ($result = $this->checkIfPicklistFieldNameReserved($value))) {
						$message = \App\Language::translate('LBL_FIELD_NAME_IS_RESERVED', 'Settings::LayoutEditor');
						$code = 512;
					}
					break;
				case 'fieldType':
					if ($result = !\in_array($value, $this->getAddSupportedFieldTypes())) {
						$message = \App\Language::translate('LBL_WRONG_FIELD_TYPE', 'Settings::LayoutEditor');
						$code = 513;
					}
					break;
				case 'pickListValues':
					foreach ($value as $val) {
						if (($result = preg_match('/[\<\>\"\#\,]/', $val)) || ($result = preg_match('/[\<\>\"\#\,]/', \App\Purifier::decodeHtml($val)))) {
							$message = \App\Language::translateArgs('ERR_SPECIAL_CHARACTERS_NOT_ALLOWED', 'Other.Exceptions', '<>"#,');
							$code = 512;
						} elseif ($result = \strlen($val) > 200) {
							$message = \App\Language::translate('ERR_EXCEEDED_NUMBER_CHARACTERS', 'Other.Exceptions');
							$code = 512;
						}
					}
					if (\count($value) !== \count(array_unique(array_map('strtolower', $value)))) {
						$message = \App\Language::translate('LBL_DUPLICATES_VALUES_FOUND', 'Other.Exceptions');
						$code = 512;
					}
					break;
				default:
					break;
			}
			if ($result) {
				if ($throw) {
					throw new \App\Exceptions\AppException($message, $code);
				}
				return [$key => $message];
			}
		}
		return $result;
	}

	/**
	 * Add field.
	 *
	 * @param string $fieldType
	 * @param int    $blockId
	 * @param array  $params
	 *
	 * @throws Exception
	 *
	 * @return \Settings_LayoutEditor_Field_Model
	 */
	public function addField($fieldType, $blockId, $params)
	{
		$label = $params['fieldLabel'];
		$name = strtolower($params['fieldName']);
		$pickListValues = [];
		if (\array_key_exists('pickListValues', $params)) {
			$pickListValues = $params['pickListValues'] = \is_string($params['pickListValues']) ? [$params['pickListValues']] : $params['pickListValues'];
		}
		$fieldParams = '';
		$this->validate($params);
		$moduleName = $this->getName();
		$tableName = $this->getTableName($params['fieldTypeList']);
		switch ($fieldType) {
			case 'Tree':
			case 'CategoryMultipicklist':
				$fieldParams = (int) $params['tree'];
				break;
			case 'MultiReferenceValue':
				$fieldParams = [
					'module' => $params['MRVModule'],
					'field' => $params['MRVField'],
					'filterField' => $params['MRVFilterField'] ?? null,
					'filterValue' => $params['MRVFilterValue'] ?? null,
				];
				\App\Db::getInstance()->createCommand()->insert('s_#__multireference', ['source_module' => $moduleName, 'dest_module' => $params['MRVModule']])->execute();
				break;
			case 'ServerAccess':
				$fieldParams = (int) $params['server'];
				break;
			case 'Token':
				(new \App\BatchMethod(['method' => '\App\Fields\Token::setTokens', 'params' => [$name, $moduleName]]))->save();
				break;
			case 'MultiReference':
				$fieldParams = [
					'module' => $params['referenceModule']
				];
				break;
			default:
				break;
		}
		$details = $this->getTypeDetailsForAddField($fieldType, $params);
		$fieldModel = new Settings_LayoutEditor_Field_Model();
		$fieldModel->set('name', $name)
			->set('table', $tableName)
			->set('generatedtype', $params['generatedtype'] ?? 2)
			->set('helpinfo', $params['helpinfo'] ?? '')
			->set('uitype', $details['uitype'])
			->set('label', $label)
			->set('typeofdata', $details['typeofdata'])
			->set('quickcreate', $params['quickcreate'] ?? 1)
			->set('summaryfield', $params['summaryfield'] ?? 0)
			->set('header_field', $params['header_field'] ?? null)
			->set('fieldparams', $params['fieldparams'] ?? ($fieldParams ? \App\Json::encode($fieldParams) : ''))
			->set('columntype', $details['dbType']);
		if ('Editor' === $fieldType) {
			$fieldModel->set('maximumlength', $params['fieldLength'] ?? null);
		}
		if (isset($details['displayType']) || isset($params['displayType'])) {
			$fieldModel->set('displaytype', $params['displayType'] ?? $details['displayType']);
		}
		$blockModel = Vtiger_Block_Model::getInstance($blockId, $moduleName);
		$blockModel->addField($fieldModel);
		if ('Phone' === $fieldType) {
			$fieldInstance = new vtlib\Field();
			$fieldInstance->name = $name . '_extra';
			$fieldInstance->table = $tableName;
			$fieldInstance->label = 'FL_PHONE_CUSTOM_INFORMATION';
			$fieldInstance->column = $name . '_extra';
			$fieldInstance->uitype = 1;
			$fieldInstance->displaytype = 3;
			$fieldInstance->maxlengthtext = 100;
			$fieldInstance->typeofdata = 'V~O';
			$fieldInstance->save($blockModel);
		}
		if ('Picklist' === $fieldType || 'MultiSelectCombo' === $fieldType) {
			$fieldModel->setPicklistValues($pickListValues);
		}
		if ('Related1M' === $fieldType) {
			if (!\is_array($params['referenceModule'])) {
				$moduleList[] = $params['referenceModule'];
			} else {
				$moduleList = $params['referenceModule'];
			}
			$fieldModel->setRelatedModules($moduleList);
			foreach ($moduleList as $module) {
				$targetModule = vtlib\Module::getInstance($module);
				$targetModule->setRelatedList($this, $moduleName, ['Add'], 'getDependentsList', $name);
			}
		}
		App\Cache::clear();
		return $fieldModel;
	}

	/**
	 * Function defines details of the created field.
	 *
	 * @param string $fieldType
	 * @param array  $params
	 *
	 * @return (sting|int)[]
	 */
	public function getTypeDetailsForAddField($fieldType, $params)
	{
		$displayType = 1;
		$importerType = new \App\Db\Importers\Base();
		switch ($fieldType) {
			case 'Text':
				$fieldLength = $params['fieldLength'];
				$uichekdata = 'V~O~LE~' . $fieldLength;
				$uitype = 1;
				$type = $importerType->stringType($fieldLength)->defaultValue('');
				break;
			case 'AdvPercentage':
				$uitype = 365;
				// no break
			case 'Decimal':
				$fieldLength = $params['fieldLength'];
				$decimal = $params['decimal'];
				$uitype = $uitype ?? 7;
				$dbfldlength = $fieldLength + $decimal + 1;
				$type = $importerType->decimal($dbfldlength, $decimal);
				// Fix for http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/6363
				$uichekdata = 'NN~O';
				break;
			case 'Percent':
				$uitype = 9;
				$type = $importerType->decimal(5, 2);
				$uichekdata = 'N~O~2~2';
				break;
			case 'Currency':
				$fieldLength = $params['fieldLength'];
				$decimal = $params['decimal'];
				$uitype = 71;
				if (1 == $fieldLength) {
					$dbfldlength = $fieldLength + $decimal + 2;
				} else {
					$dbfldlength = $fieldLength + $decimal + 1;
				}
				$decimal = $decimal + 3;
				$type = $importerType->decimal($dbfldlength, $decimal);
				$uichekdata = 'N~O';
				break;
			case 'Date':
				$uichekdata = 'D~O';
				$uitype = 5;
				$type = $importerType->date();
				break;
			case 'Email':
				$uitype = 13;
				$type = $importerType->stringType(100)->defaultValue('');
				$uichekdata = 'E~O';
				break;
			case 'Time':
				$uitype = 14;
				$type = $importerType->time();
				$uichekdata = 'T~O';
				break;
			case 'Phone':
				$uitype = 11;
				$type = $importerType->stringType(30)->defaultValue('');
				$uichekdata = 'V~O';
				break;
			case 'Picklist':
				$uitype = 16;
				if (!empty($params['isRoleBasedPickList'])) {
					$uitype = 15;
				}
				$type = $importerType->stringType()->defaultValue('');
				$uichekdata = 'V~O';
				break;
			case 'URL':
				$uitype = 17;
				$type = $importerType->stringType()->defaultValue('');
				$uichekdata = 'V~O';
				break;
			case 'Checkbox':
				$uitype = 56;
				$type = $importerType->boolean()->defaultValue(false);
				$uichekdata = 'C~O';
				break;
			case 'TextArea':
				$uitype = 21;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'MultiSelectCombo':
				$uitype = 33;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'Skype':
				$uitype = 85;
				$type = $importerType->stringType()->defaultValue('');
				$uichekdata = 'V~O';
				break;
			case 'Integer':
				$fieldLength = $params['fieldLength'];
				$uitype = 7;
				$type = $importerType->integer($fieldLength)->defaultValue(0);
				$uichekdata = 'I~O';
				break;
			case 'Related1M':
				$uitype = 10;
				$type = $importerType->integer(10)->defaultValue(0)->unsigned();
				$uichekdata = 'V~O';
				break;
			case 'Editor':
				$fieldLength = $params['fieldLength'];
				$uitype = 300;
				$type = $importerType->text($fieldLength);
				$uichekdata = 'V~O';
				break;
			case 'Tree':
				$uitype = 302;
				$type = $importerType->stringType(30)->defaultValue('');
				$uichekdata = 'V~O';
				break;
			case 'MultiReferenceValue':
				$uitype = 305;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				$displayType = 5;
				break;
			case 'MultiImage':
				$uitype = 311;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'Image':
				$uitype = 69;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'CategoryMultipicklist':
				$uitype = 309;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'DateTime':
				$uichekdata = 'DT~O';
				$uitype = 79;
				$type = $importerType->dateTime();
				break;
			case 'Country':
				$uitype = 35;
				$uichekdata = 'V~O';
				$type = $importerType->stringType(255);
				break;
			case 'Twitter':
				$fieldLength = Vtiger_Twitter_UIType::MAX_LENGTH;
				$uichekdata = 'V~O~LE~' . $fieldLength;
				$uitype = 313;
				$type = $importerType->stringType($fieldLength)->defaultValue('');
				break;
			case 'MultiEmail':
				$uitype = 314;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'Smtp':
				$uitype = 316;
				$uichekdata = 'V~O';
				$type = $importerType->integer()->defaultValue(null)->unsigned();
				break;
			case 'ServerAccess':
				$uitype = 318;
				$uichekdata = 'C~O';
				$type = $importerType->boolean()->defaultValue(false);
				break;
			case 'MultiDomain':
				$uitype = 319;
				$uichekdata = 'V~O';
				$type = $importerType->text();
				break;
			case 'RangeTime':
				$uitype = 308;
				$uichekdata = 'I~O';
				$type = $importerType->integer()->null();
				break;
			case 'Token':
				$uitype = 324;
				$uichekdata = 'V~O';
				$displayType = 3;
				$type = $importerType->stringType(Vtiger_Token_UIType::MAX_LENGTH)->defaultValue('');
				break;
			case 'MultiAttachment':
				$uitype = 330;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			case 'MultiReference':
				$uitype = 321;
				$type = $importerType->text();
				$uichekdata = 'V~O';
				break;
			default:
				break;
		}
		return [
			'uitype' => $uitype,
			'typeofdata' => $uichekdata,
			'dbType' => $type,
			'displayType' => $displayType,
		];
	}

	public function getTableName($type)
	{
		if (\is_int($type)) {
			$focus = CRMEntity::getInstance($this->getName());
			if (0 == $type) {
				$tableName = $focus->table_name;
			} elseif (1 == $type) {
				if (isset($focus->customFieldTable)) {
					$tableName = $focus->customFieldTable[0];
				} else {
					$tableName = $focus->table_name . 'cf';
				}
			}
		} else {
			$tableName = $type;
		}
		return $tableName;
	}

	/**
	 * Check field name characters.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function checkFieldNameCharacters($name): bool
	{
		return preg_match('#[^a-z0-9_]#is', $name) || !preg_match('/[a-z]/i', $name) || false !== strpos($name, ' ');
	}

	/**
	 * Check if label exists.
	 *
	 * @param string $fieldLabel
	 *
	 * @return bool
	 */
	public function checkFieldLabelExists(string $fieldLabel): bool
	{
		return (new \App\Db\Query())->from('vtiger_field')->where(['fieldlabel' => $fieldLabel, 'tabid' => $this->getId()])->exists();
	}

	/**
	 * Check if field exists.
	 *
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public function checkFieldNameExists(string $fieldName): bool
	{
		return (new \App\Db\Query())->from('vtiger_field')->where(['tabid' => $this->getId()])
			->andWhere(['or', ['fieldname' => $fieldName], ['columnname' => $fieldName]])->exists();
	}

	/**
	 * Check if the field name is reserved.
	 *
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public function checkFieldNameIsAnException(string $fieldName)
	{
		return \in_array($fieldName, [
			'id', 'seq', 'header_type', 'header_class',
			'module', 'parent', 'action', 'mode', 'view', 'selected_ids',
			'excluded_ids', 'search_params', 'search_key', 'page', 'operator',
			'source_module', 'viewname', 'sortorder', 'orderby', 'inventory', 'private', 'src_record', 'relationid', 'relation_id', 'picklist', 'overwritten_shownerid', 'relationoperation', 'sourcemodule', 'sourcerecord'
		]);
	}

	public static function getSupportedModules()
	{
		if (empty(self::$supportedModules)) {
			self::$supportedModules = self::getEntityModulesList();
		}
		return self::$supportedModules;
	}

	/**
	 * Get instance by name.
	 *
	 * @param string $moduleName
	 *
	 * @return self
	 */
	public static function getInstanceByName($moduleName)
	{
		$moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		$objectProperties = get_object_vars($moduleInstance);
		$selfInstance = new self();
		foreach ($objectProperties as $properName => $propertyValue) {
			$selfInstance->{$properName} = $propertyValue;
		}
		return $selfInstance;
	}

	/**
	 * Function to get Entity module names list.
	 *
	 * @return string[] List of Entity modules
	 */
	public static function getEntityModulesList()
	{
		$restrictedModules = ['Integration', 'Dashboard'];
		return (new \App\Db\Query())->select(['name', 'module' => 'name'])->from('vtiger_tab')->where(['presence' => [0, 2], 'isentitytype' => 1])->andWhere(['not in', 'name', $restrictedModules])->createCommand()->queryAllByGroup();
	}

	/**
	 * Function to check field is editable or not.
	 *
	 * @return bool
	 */
	public function isSortableAllowed()
	{
		return true;
	}

	/**
	 * Function to check blocks are sortable for the module.
	 *
	 * @return bool
	 */
	public function isBlockSortableAllowed()
	{
		if ('ModComments' === $this->getName()) {
			return false;
		}
		return true;
	}

	/**
	 * Function to check fields are sortable for the block.
	 *
	 * @param mixed $blockName
	 *
	 * @return bool
	 */
	public function isFieldsSortableAllowed($blockName)
	{
		$moduleName = $this->getName();
		$blocksEliminatedArray = ['HelpDesk' => ['LBL_TICKET_RESOLUTION', 'LBL_COMMENTS'],
			'Faq' => ['LBL_COMMENT_INFORMATION'],
			'Calendar' => ['LBL_TASK_INFORMATION', 'LBL_DESCRIPTION_INFORMATION', 'LBL_REMINDER_INFORMATION', 'LBL_RECURRENCE_INFORMATION'],
		];
		if (\in_array($moduleName, ['HelpDesk', 'Faq'])) {
			if (!empty($blocksEliminatedArray[$moduleName])) {
				if (\in_array($blockName, $blocksEliminatedArray[$moduleName])) {
					return false;
				}
			} else {
				return false;
			}
		}
		return true;
	}

	public function getRelations()
	{
		if (null === $this->relations) {
			$this->relations = Vtiger_Relation_Model::getAllRelations($this, false, true, true);
		}
		return $this->relations;
	}

	/**
	 * Function returns available templates for tree type field.
	 *
	 * @param string $sourceModule
	 *
	 * @return array
	 */
	public function getTreeTemplates($sourceModule)
	{
		$sourceModule = \App\Module::getModuleId($sourceModule);
		$query = (new \App\Db\Query())->select(['templateid', 'name'])->from('vtiger_trees_templates')->where(['tabid' => $sourceModule])->orWhere(['like', 'share', ",$sourceModule,"]);
		$treeList = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$treeList[$row['templateid']] = $row['name'];
		}
		$dataReader->close();

		return $treeList;
	}

	public static function getRelationsTypes(?string $moduleName = null)
	{
		$types = [
			'getRelatedList' => 'PLL_RELATED_LIST',
			//'getDependentsList' => 'PLL_DEPENDENTS_LIST',
			'getManyToMany' => 'PLL_SPLITED_RELATED_LIST',
			'getAttachments' => 'PLL_ATTACHMENTS',
			// 'getActivities' => 'PLL_ACTIVITIES',
			'getEmails' => 'PLL_EMAILS',
		];
		if ('OSSMailView' === $moduleName) {
			$types['getRecordToMails'] = 'PLL_RECORD_TO_MAILS';
		}
		return $types;
	}

	public static function getRelationsActions()
	{
		return [
			'ADD' => 'PLL_ADD',
			'SELECT' => 'PLL_SELECT',
		];
	}

	/**
	 * Update related view type.
	 *
	 * @param int      $relationId
	 * @param string[] $type
	 */
	public static function updateRelatedViewType($relationId, $type)
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_relatedlists', ['view_type' => implode(',', $type)], ['relation_id' => $relationId])->execute();
		\App\Relation::clearCacheById($relationId);
	}

	/**
	 * Get related view types.
	 *
	 * @return string[]
	 */
	public static function getRelatedViewTypes()
	{
		return static::$relatedViewType;
	}

	/**
	 * Check if picklist field can have that name.
	 *
	 * @param string $fieldName
	 *
	 * @return bool
	 */
	public function checkIfPicklistFieldNameReserved(string $fieldName): bool
	{
		return (
			\App\Fields\Picklist::isPicklistExist($fieldName)
			&& !(new \App\Db\Query())->from('vtiger_field')->where(['or', ['fieldname' => $fieldName], ['columnname' => $fieldName]])->exists()
		) || \in_array($fieldName, (new \App\Db\Query())->select(['fieldname'])->from('vtiger_field')->where(['tabid' => \App\Module::getModuleId('Users'), 'uitype' => [16, 15, 33, 115]])->column());
	}

	/**
	 * Get missing system fields.
	 *
	 * @return \Vtiger_Field_Model[]
	 */
	public function getMissingSystemFields(): array
	{
		$fields = $this->getFields();
		$systemFields = \App\Field::SYSTEM_FIELDS;
		$missingFields = [];
		foreach (Settings_WebserviceApps_Module_Model::getServers() as $id => $field) {
			$name = 'share_externally_' . $id;
			$systemFields[$name] = array_merge($systemFields['share_externally'], [
				'name' => $name,
				'column' => $name,
				'label' => $field['name'] . ' (' . \App\Language::translate($field['type'], 'Settings:WebserviceApps') . ')',
				'fieldparams' => $id,
			]);
		}
		unset($systemFields['share_externally']);
		foreach ($systemFields as $name => $field) {
			$validationConditions = $field['validationConditions'];
			if ($validationConditions === ['name']) {
				$exist = isset($fields[$name]);
			} else {
				$exist = true;
				foreach ($validationConditions as $validationCondition) {
					$status = true;
					foreach ($fields as $fieldModel) {
						if ($fieldModel->get($validationCondition) == $field[$validationCondition]) {
							$status = false;
							continue 2;
						}
					}
					$exist = !$status;
				}
			}
			if (!$exist) {
				unset($field['validationConditions']);
				$missingFields[$name] = \Vtiger_Field_Model::init($this->name, $field, $field['name']);
			}
		}
		return $missingFields;
	}

	/**
	 * Create system field.
	 *
	 * @param string $sysName
	 * @param int    $blockId
	 * @param array  $params
	 *
	 * @return void
	 */
	public function addSystemField(string $sysName, int $blockId, array $params = []): void
	{
		$missingSystemFields = $this->getMissingSystemFields();
		if (empty($missingSystemFields[$sysName])) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_DUPLICATE_FIELD_EXISTS', 'Settings::LayoutEditor'), 512);
		}
		$fieldModel = $missingSystemFields[$sysName];
		if ($params) {
			foreach ($params as $key => $value) {
				$fieldModel->set($key, $value);
			}
		}
		if ($this->checkFieldLabelExists($fieldModel->get('name'))) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_DUPLICATE_FIELD_EXISTS', 'Settings::LayoutEditor'), 513);
		}
		if ($this->checkFieldNameCharacters($fieldModel->get('name'))) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_INVALIDCHARACTER', 'Settings::LayoutEditor'), 512);
		}
		if ($this->checkFieldNameExists($fieldModel->get('name'))) {
			throw new \App\Exceptions\AppException(\App\Language::translate('LBL_DUPLICATE_FIELD_EXISTS', 'Settings::LayoutEditor'), 512);
		}
		$blockModel = Vtiger_Block_Model::getInstance($blockId, $this->name);
		$blockModel->addField($fieldModel);
	}

	/**
	 * Get fields for webservice apps.
	 *
	 * @param int $webserviceApp
	 *
	 * @return array
	 */
	public function getFieldsForWebserviceApps(int $webserviceApp): array
	{
		return (new \App\Db\Query())->from('w_#__fields_server')->where(['serverid' => $webserviceApp])->indexBy('fieldid')->all(\App\Db::getInstance('webservice')) ?: [];
	}
}
