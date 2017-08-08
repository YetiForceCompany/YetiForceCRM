<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ******************************************************************************** */

/**
 * Description of EmailTemplateUtils
 *
 * @author mak
 */
class EmailTemplate
{

	/**
	 * Module
	 * @var string
	 */
	protected $module;

	/**
	 * Raw description
	 * @var string
	 */
	protected $rawDescription;

	/**
	 * Processed description
	 * @var string
	 */
	protected $processedDescription;

	/**
	 * Record id
	 * @var int
	 */
	protected $recordId;

	/**
	 * Processed
	 * @var bool
	 */
	protected $processed;

	/**
	 * Template fields
	 * @var array
	 */
	protected $templateFields;

	/**
	 * User
	 * @var int
	 */
	protected $user;

	/**
	 * Processed modules
	 * @var array
	 */
	protected $processedmodules;

	/**
	 * Referenced fields
	 * @var array
	 */
	protected $referencedFields;

	/**
	 * Class constructor
	 * @param string $module
	 * @param string $description
	 * @param int $recordId
	 * @param int $user
	 */
	public function __construct($module, $description, $recordId, $user)
	{
		$this->module = $module;
		$this->recordId = $recordId;
		$this->processed = false;
		$this->user = $user;
		$this->setDescription($description);
	}

	/**
	 * Set description
	 * @param string $description
	 */
	public function setDescription($description)
	{
		// Because if we have two dollars like this "$$" it's not working because it'll be like escape char
		$description = preg_replace("/\\$\\$/", "$ $", $description);
		$this->rawDescription = $description;
		$this->processedDescription = $description;
		$result = preg_match_all("/\\$(?:[a-zA-Z0-9]+)-(?:[a-zA-Z0-9]+)(?:_[a-zA-Z0-9]+)?(?::[a-zA-Z0-9]+)?(?:_[a-zA-Z0-9]+)?\\$/", $this->rawDescription, $matches);
		if ($result != 0) {
			$templateVariablePair = $matches[0];
			$this->templateFields = [];
			$countTemplateVariablePair = count($templateVariablePair);
			for ($i = 0; $i < $countTemplateVariablePair; $i++) {
				$templateVariablePair[$i] = str_replace('$', '', $templateVariablePair[$i]);
				list($module, $columnName) = explode('-', $templateVariablePair[$i]);
				list($parentColumn, $childColumn) = explode(':', $columnName);
				$this->templateFields[$module][] = $parentColumn;
				$this->referencedFields[$parentColumn][] = $childColumn;
				$this->processedmodules[$module] = false;
			}
			$this->processed = false;
		}
	}

	/**
	 * Get template variable list for module
	 * @param string $module
	 * @return array
	 */
	private function getTemplateVariableListForModule($module)
	{
		return $this->templateFields[strtolower($module)];
	}

	/**
	 * Process
	 * @param array $params
	 * @return null
	 */
	public function process($params)
	{
		$module = $this->module;
		$recordId = $this->recordId;
		$variableList = $this->getTemplateVariableListForModule($module);
		$handler = vtws_getModuleHandlerFromName($module, $this->user);
		$meta = $handler->getMeta();
		$fieldColumnMapping = $meta->getFieldColumnMapping();
		$columnTableMapping = $meta->getColumnTableMapping();

		if ($this->isProcessingReferenceField($params)) {
			$parentFieldColumnMapping = $meta->getFieldColumnMapping();
			$module = $params['referencedMeta']->getEntityName();
			if ($this->processedmodules[$module] || (!$this->isModuleActive($module))) {
				return;
			}
			$recordId = $params['id'];

			$meta = $params['referencedMeta'];
			$fieldColumnMapping = $meta->getFieldColumnMapping();
			$columnTableMapping = $meta->getColumnTableMapping();
			$referenceColumn = $parentFieldColumnMapping[$params['field']];
			$variableList = $this->referencedFields[$referenceColumn];
		}

		$tableList = [];
		$columnList = [];
		$allColumnList = $meta->getUserAccessibleColumns();
		$fieldList = [];
		if (count($variableList) > 0) {
			foreach ($variableList as $column) {
				if (in_array($column, $allColumnList)) {
					$fieldList[] = array_search($column, $fieldColumnMapping);
					$columnList[] = $column;
				}
			}
			foreach ($fieldList as $field) {
				if (!empty($columnTableMapping[$fieldColumnMapping[$field]])) {
					$tableList[$columnTableMapping[$fieldColumnMapping[$field]]] = '';
				}
			}
			$tableList = array_keys($tableList);
			$defaultTableList = $meta->getEntityDefaultTableList();
			foreach ($defaultTableList as $defaultTable) {
				if (!in_array($defaultTable, $tableList)) {
					$tableList[] = $defaultTable;
				}
			}

			if (count($tableList) > 0 && count($columnList) > 0) {
				$query = (new \App\Db\Query())->select($columnList)->from($tableList[0]);
				$moduleTableIndexList = $meta->getEntityTableIndexList();
				foreach ($tableList as $index => $tableName) {
					if ($tableName != $tableList[0]) {
						$query->innerJoin($tableName, $tableList[0] . '.' . $moduleTableIndexList[$tableList[0]] . ' = ' . $tableName . '.' . $moduleTableIndexList[$tableName]);
					}
				}
				//If module is Leads and if you are not selected any leads fields then query failure is happening.
				//By default we are checking where condition on base table.
				if ($module == 'Leads' && !in_array('vtiger_leaddetails', $tableList)) {
					$query->innerJoin('vtiger_leaddetails', 'vtiger_leaddetails.leadid = vtiger_crmentity.crmid');
				}

				$deleteQuery = $meta->getEntityDeletedQuery();
				if (!empty($deleteQuery)) {
					$query->where($meta->getEntityDeletedQuery());
				}
				$query->andWhere([$tableList[0] . '.' . $moduleTableIndexList[$tableList[0]] => $recordId]);
				//assuming there can only be one row.
				$values = [];
				$dataReader = $query->createCommand()->query();
				while ($row = $dataReader->read()) {
					foreach ($fieldList as $field) {
						$values[$field] = $row[$fieldColumnMapping[$field]];
					}
				}
				$moduleFields = $meta->getModuleFields();
				foreach ($moduleFields as $fieldName => $webserviceField) {
					$presence = $webserviceField->getPresence();
					if (!in_array($presence, array(0, 2))) {
						continue;
					}
					if (isset($values[$fieldName]) &&
						$values[$fieldName] !== null) {
						if (strcasecmp($webserviceField->getFieldDataType(), 'reference') === 0) {
							$details = $webserviceField->getReferenceList();
							if (count($details) == 1) {
								$referencedObjectHandler = vtws_getModuleHandlerFromName(
									$details[0], $this->user);
							} else {
								$type = \vtlib\Functions::getCRMRecordType(
										$values[$fieldName]);
								$referencedObjectHandler = vtws_getModuleHandlerFromName($type, $this->user);
							}
							$referencedObjectMeta = $referencedObjectHandler->getMeta();
							if (!$this->isProcessingReferenceField($params) && !empty($values[$fieldName])) {
								$this->process(array('parentMeta' => $meta, 'referencedMeta' => $referencedObjectMeta, 'field' => $fieldName, 'id' => $values[$fieldName]));
							}
							$values[$fieldName] = $referencedObjectMeta->getName(vtws_getId(
									$referencedObjectMeta->getEntityId(), $values[$fieldName]));
						} elseif (strcasecmp($webserviceField->getFieldDataType(), 'owner') === 0) {
							$referencedObjectHandler = vtws_getModuleHandlerFromName(
								vtws_getOwnerType($values[$fieldName]), $this->user);
							$referencedObjectMeta = $referencedObjectHandler->getMeta();
							/*
							 * operation supported for format $module-parentcolumn:childcolumn$
							 */
							if (in_array($fieldColumnMapping[$fieldName], array_keys($this->referencedFields))) {
								$this->process(array('parentMeta' => $meta, 'referencedMeta' => $referencedObjectMeta, 'field' => $fieldName, 'id' => $values[$fieldName], 'owner' => true));
							}

							$values[$fieldName] = $referencedObjectMeta->getName(vtws_getId(
									$referencedObjectMeta->getEntityId(), $values[$fieldName]));
						} elseif (strcasecmp($webserviceField->getFieldDataType(), 'picklist') === 0) {
							$values[$fieldName] = \App\Language::translate(
									$values[$fieldName], $module);
						} elseif (strcasecmp($fieldName, 'salutationtype') === 0 && $webserviceField->getUIType() == '55') {
							$values[$fieldName] = \App\Language::translate(
									$values[$fieldName], $module);
						} elseif (strcasecmp($webserviceField->getFieldDataType(), 'datetime') === 0) {
							$values[$fieldName] = $values[$fieldName] . ' ' . DateTimeField::getDBTimeZone();
						}
					}
				}

				if (!$this->isProcessingReferenceField($params)) {
					foreach ($columnList as $column) {
						$needle = '$' . strtolower($this->module) . "-$column$";
						$this->processedDescription = str_replace($needle, $values[array_search($column, $fieldColumnMapping)], $this->processedDescription);
					}
					// Is process Description will send false even that module don't have reference record set
					$this->processedDescription = preg_replace("/\\$(?:[a-zA-Z0-9]+)-(?:[a-zA-Z0-9]+)(?:_[a-zA-Z0-9]+)?(?::[a-zA-Z0-9]+)(?:[a-zA-Z0-9]+)?(?:_[a-zA-Z0-9]+)?\\$/", '', $this->processedDescription);
				} else {
					foreach ($columnList as $column) {
						$needle = '$' . strtolower($this->module) . '-' . $parentFieldColumnMapping[$params['field']] . ':' . $column . '$';
						$this->processedDescription = str_replace($needle, $values[array_search($column, $fieldColumnMapping)], $this->processedDescription);
					}
					if (!$params['owner'])
						$this->processedmodules[$module] = true;
				}
			}
		}
		$this->processed = true;
	}

	/**
	 * Check if is processing reference field
	 * @param array $params
	 * @return boolean
	 */
	public function isProcessingReferenceField($params)
	{
		if (!empty($params['referencedMeta']) && (!empty($params['id'])) && (!empty($params['field']))
		) {
			return true;
		}

		return false;
	}

	/**
	 * Get processed description
	 * @return string
	 */
	public function getProcessedDescription()
	{
		if (!$this->processed) {
			$this->process(null);
		}
		return $this->processedDescription;
	}

	/**
	 * Check if module is active
	 * @param string $module
	 * @return boolean
	 */
	public function isModuleActive($module)
	{
		include_once 'include/utils/VtlibUtils.php';
		if (\App\Module::isModuleActive($module) && ((isPermitted($module, 'EditView') == 'yes'))) {
			return true;
		}
		return false;
	}

	/**
	 * Check if module field is active
	 * @param string $field
	 * @param string $mod
	 * @return boolean
	 */
	public function isActive($field, $mod)
	{
		$tabid = \App\Module::getModuleId($mod);
		$result = (new \App\Db\Query())->from('vtiger_field')->where(['fieldname' => $field, 'tabid' => $tabid, 'presence' => [0, 2]])->count();
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
}
