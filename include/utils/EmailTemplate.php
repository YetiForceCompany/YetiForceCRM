<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************* */

require_once 'include/events/SqlResultIterator.inc';

/**
 * Description of EmailTemplateUtils
 *
 * @author mak
 */
class EmailTemplate {

	protected $module;
	protected $rawDescription;
	protected $processedDescription;
	protected $recordId;
	protected $processed;
	protected $templateFields;
	protected $user;
	protected $processedmodules;
	protected $referencedFields;

	public function __construct($module, $description, $recordId, $user) {
		$this->module = $module;
		$this->recordId = $recordId;
		$this->processed = false;
		$this->user = $user;
		$this->setDescription($description);
	}

	public function setDescription($description) {
        // Because if we have two dollars like this "$$" it's not working because it'll be like escape char
        $description = preg_replace("/\\$\\$/","$ $",$description);
		$this->rawDescription = $description;
		$this->processedDescription = $description;
        $result = preg_match_all("/\\$(?:[a-zA-Z0-9]+)-(?:[a-zA-Z0-9]+)(?:_[a-zA-Z0-9]+)?(?::[a-zA-Z0-9]+)?(?:_[a-zA-Z0-9]+)?\\$/", $this->rawDescription, $matches);
        if($result != 0){
            $templateVariablePair = $matches[0];
            $this->templateFields = Array();
            for ($i = 0; $i < count($templateVariablePair); $i++) {
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

	private function getTemplateVariableListForModule($module) {
		return $this->templateFields[strtolower($module)];
	}

	public function process($params) {
		$module = $this->module;
		$recordId = $this->recordId;
		$variableList = $this->getTemplateVariableListForModule($module);
		$handler = vtws_getModuleHandlerFromName($module, $this->user);
		$meta = $handler->getMeta();
		$referenceFields = $meta->getReferenceFieldDetails();
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
			$referenceFields = $meta->getReferenceFieldDetails();
			$fieldColumnMapping = $meta->getFieldColumnMapping();
			$columnTableMapping = $meta->getColumnTableMapping();
			$referenceColumn = $parentFieldColumnMapping[$params['field']];
			$variableList = $this->referencedFields[$referenceColumn];
		}

		$tableList = array();
		$columnList = array();
		$allColumnList = $meta->getUserAccessibleColumns();
		$fieldList = array();
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
				$sql = 'select ' . implode(', ', $columnList) . ' from ' . $tableList[0];
				$moduleTableIndexList = $meta->getEntityTableIndexList();
				foreach ($tableList as $index => $tableName) {
					if ($tableName != $tableList[0]) {
						$sql .=' INNER JOIN ' . $tableName . ' ON ' . $tableList[0] . '.' .
								$moduleTableIndexList[$tableList[0]] . '=' . $tableName . '.' .
								$moduleTableIndexList[$tableName];
					}
				}
				//If module is Leads and if you are not selected any leads fields then query failure is happening.
				//By default we are checking where condition on base table.
				if($module == 'Leads' && !in_array('vtiger_leaddetails', $tableList)){
					$sql .=' INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid';
				}
				
				$sql .= ' WHERE';
				$deleteQuery = $meta->getEntityDeletedQuery();
				if (!empty($deleteQuery)) {
					$sql .= ' ' . $meta->getEntityDeletedQuery() . ' AND';
				}
				$sql .= ' ' . $tableList[0] . '.' . $moduleTableIndexList[$tableList[0]] . '=?';
				$sqlparams = array($recordId);
				$db = PearDatabase::getInstance();
				$result = $db->pquery($sql, $sqlparams);
				$it = new SqlResultIterator($db, $result);
			//assuming there can only be one row.
				$values = array();
				foreach ($it as $row) {
					foreach ($fieldList as $field) {
						$values[$field] = $row->get($fieldColumnMapping[$field]);
					}
				}
				$moduleFields = $meta->getModuleFields();
				foreach ($moduleFields as $fieldName => $webserviceField) {
                    $presence = $webserviceField->getPresence();
                    if(!in_array($presence,array(0,2))){
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
								$type = getSalesEntityType(
										$values[$fieldName]);
								$referencedObjectHandler = vtws_getModuleHandlerFromName($type,
										$this->user);
							}
							$referencedObjectMeta = $referencedObjectHandler->getMeta();
							if (!$this->isProcessingReferenceField($params) && !empty($values[$fieldName])) {
								$this->process(array('parentMeta' => $meta, 'referencedMeta' => $referencedObjectMeta, 'field' => $fieldName, 'id' => $values[$fieldName]));
							}
							$values[$fieldName] =
									$referencedObjectMeta->getName(vtws_getId(
									$referencedObjectMeta->getEntityId(),
									$values[$fieldName]));
						} elseif (strcasecmp($webserviceField->getFieldDataType(), 'owner') === 0) {
							$referencedObjectHandler = vtws_getModuleHandlerFromName(
									vtws_getOwnerType($values[$fieldName]),
									$this->user);
							$referencedObjectMeta = $referencedObjectHandler->getMeta();
							/*
							* operation supported for format $module-parentcolumn:childcolumn$
							*/
							if (in_array($fieldColumnMapping[$fieldName], array_keys($this->referencedFields))) {
								$this->process(array('parentMeta' => $meta, 'referencedMeta' => $referencedObjectMeta, 'field' => $fieldName, 'id' => $values[$fieldName], 'owner' => true));
							}

							$values[$fieldName] =
									$referencedObjectMeta->getName(vtws_getId(
									$referencedObjectMeta->getEntityId(),
									$values[$fieldName]));
						} elseif (strcasecmp($webserviceField->getFieldDataType(), 'picklist') === 0) {
							$values[$fieldName] = getTranslatedString(
									$values[$fieldName], $module);
						} elseif (strcasecmp($fieldName, 'salutationtype') === 0 && $webserviceField->getUIType() == '55'){
							$values[$fieldName] = getTranslatedString(
									$values[$fieldName], $module);
						} elseif (strcasecmp($webserviceField->getFieldDataType(), 'datetime') === 0) {
							$values[$fieldName] = $values[$fieldName] . ' ' . DateTimeField::getDBTimeZone();
						}
					}
				}

				if (!$this->isProcessingReferenceField($params)) {
					foreach ($columnList as $column) {
						$needle = '$' . strtolower($this->module) . "-$column$";
						$this->processedDescription = str_replace($needle,
								$values[array_search($column, $fieldColumnMapping)], $this->processedDescription);
					}
                    // Is process Description will send false even that module don't have reference record set
                    $this->processedDescription = preg_replace("/\\$(?:[a-zA-Z0-9]+)-(?:[a-zA-Z0-9]+)(?:_[a-zA-Z0-9]+)?(?::[a-zA-Z0-9]+)(?:[a-zA-Z0-9]+)?(?:_[a-zA-Z0-9]+)?\\$/", '', $this->processedDescription);
				} else {
					foreach ($columnList as $column) {
						$needle = '$' . strtolower($this->module) . '-' . $parentFieldColumnMapping[$params['field']] . ':' . $column . '$';
						$this->processedDescription = str_replace($needle,
								$values[array_search($column, $fieldColumnMapping)], $this->processedDescription);
					}
					if (!$params['owner'])
						$this->processedmodules[$module] = true;
				}
			}
		}
		$this->processed = true;
	}

	public function isProcessingReferenceField($params) {
		if (!empty($params['referencedMeta'])
				&& (!empty($params['id']))
				&& (!empty($params['field']))
		) {
			return true;
		}

		return false;
	}

	public function getProcessedDescription() {
		if (!$this->processed) {
			$this->process(null);
		}
		return $this->processedDescription;
	}

	public function isModuleActive($module) {
		include_once 'include/utils/VtlibUtils.php';
		if (vtlib_isModuleActive($module) && ((isPermitted($module, 'EditView') == 'yes'))) {
			return true;
		}
		return false;
	}

	public function isActive($field, $mod) {
		global $adb;
		$tabid = getTabid($mod);
		$query = 'select * from vtiger_field where fieldname = ?  and tabid = ? and presence in (0,2)';
		$res = $adb->pquery($query, array($field, $tabid));
		$rows = $adb->num_rows($res);
		if ($rows > 0) {
			return true;
		}else
			return false;
	}

}

?>