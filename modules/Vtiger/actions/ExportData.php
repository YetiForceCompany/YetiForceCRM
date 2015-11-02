<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_ExportData_Action extends Vtiger_Mass_Action
{

	function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Export')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function is called by the controller
	 * @param Vtiger_Request $request
	 */
	function process(Vtiger_Request $request)
	{
		$this->ExportData($request);
	}

	private $moduleInstance;
	private $focus;

	/**
	 * Function exports the data based on the mode
	 * @param Vtiger_Request $request
	 */
	function ExportData(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$moduleName = $request->get('source_module');

		$this->moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		$this->moduleFieldInstances = $this->moduleInstance->getFields();
		$this->focus = CRMEntity::getInstance($moduleName);

		$query = $this->getExportQuery($request);
		$result = $db->query($query);

		$headers = [];
		//Query generator set this when generating the query
		if (!empty($this->accessibleFields)) {
			$accessiblePresenceValue = [0, 2];
			foreach ($this->accessibleFields as $fieldName) {
				$fieldModel = $this->moduleFieldInstances[$fieldName];

				// Check added as querygenerator is not checking this for admin users
				if (isset($fieldModel) && in_array($fieldModel->get('presence'), $accessiblePresenceValue)) {
					$headers[] = $fieldModel->get('label');
				}
			}
		} else {
			foreach ($this->moduleFieldInstances as $field)
				$headers[] = $field->get('label');
		}

		$isInventory = $this->moduleInstance->isInventory();
		if ($isInventory) {
			//Get inventory headers
			$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($moduleName);
			$inventoryFields = $inventoryFieldModel->getFields();
			foreach ($inventoryFields as $field) {
				$headers[] = $field->get('label');
			}
			$table = $inventoryFieldModel->getTableName('data');
		}

		$translatedHeaders = [];
		foreach ($headers as $header)
			$translatedHeaders[] = vtranslate(html_entity_decode($header, ENT_QUOTES), $moduleName);

		$entries = [];
		while ($row = $db->fetch_array($result)) {
			$sanitizedRow = $this->sanitizeValues($row);
			if ($isInventory) {
				$resultInventory = $db->pquery('SELECT * FROM ' . $table . ' WHERE id = ? ORDER BY seq', [$row[$this->focus->table_index]]);
				if ($db->getRowCount($resultInventory)) {
					while ($inventoryRow = $db->fetch_array($resultInventory)) {
						$sanitizedInventoryRow = $this->sanitizeInventoryValues($inventoryRow, $inventoryFields);
						$entries[] = array_merge($sanitizedRow, $sanitizedInventoryRow);
					}
				} else {
					$entries[] = $sanitizedRow;
				}
			} else {
				$entries[] = $sanitizedRow;
			}
		}
		$this->output($request, $translatedHeaders, $entries);
	}

	/**
	 * Function that generates Export Query based on the mode
	 * @param Vtiger_Request $request
	 * @return <String> export query
	 */
	function getExportQuery(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$mode = $request->getMode();
		$cvId = $request->get('viewname');
		$moduleName = $request->get('source_module');

		$queryGenerator = new QueryGenerator($moduleName, $currentUser);
		$queryGenerator->initForCustomViewById($cvId);
		$fieldInstances = $this->moduleFieldInstances;

		$accessiblePresenceValue = [0, 2];
		$fields[] = 'id';
		foreach ($fieldInstances as $field) {
			// Check added as querygenerator is not checking this for admin users
			$presence = $field->get('presence');
			if (in_array($presence, $accessiblePresenceValue)) {
				$fields[] = $field->getName();
			}
		}
		$queryGenerator->setFields($fields);
		$query = $queryGenerator->getQuery();

		//TODO To be removed together with the old inventory module
		if (in_array($moduleName, getInventoryModules())) {
			$query = $this->moduleInstance->getExportQuery($this->focus, $query);
		}

		$this->accessibleFields = $queryGenerator->getFields();

		switch ($mode) {
			case 'ExportAllData' : return $query;
				break;

			case 'ExportCurrentPage' : $pagingModel = new Vtiger_Paging_Model();
				$limit = $pagingModel->getPageLimit();

				$currentPage = $request->get('page');
				if (empty($currentPage))
					$currentPage = 1;

				$currentPageStart = ($currentPage - 1) * $limit;
				if ($currentPageStart < 0)
					$currentPageStart = 0;
				$query .= ' LIMIT ' . $currentPageStart . ',' . $limit;

				return $query;
				break;

			case 'ExportSelectedRecords' : $idList = $this->getRecordsListFromRequest($request);
				$baseTable = $this->moduleInstance->get('basetable');
				$baseTableColumnId = $this->moduleInstance->get('basetableid');
				if (!empty($idList)) {
					if (!empty($baseTable) && !empty($baseTableColumnId)) {
						$idList = implode(',', $idList);
						$query .= ' AND ' . $baseTable . '.' . $baseTableColumnId . ' IN (' . $idList . ')';
					}
				} else {
					$query .= ' AND ' . $baseTable . '.' . $baseTableColumnId . ' NOT IN (' . implode(',', $request->get('excluded_ids')) . ')';
				}
				return $query;
				break;


			default : return $query;
				break;
		}
	}

	/**
	 * Function returns the export type - This can be extended to support different file exports
	 * @param Vtiger_Request $request
	 * @return <String>
	 */
	function getExportContentType(Vtiger_Request $request)
	{
		$type = $request->get('export_type');
		if (empty($type)) {
			return 'text/csv';
		}
	}

	/**
	 * Function that create the exported file
	 * @param Vtiger_Request $request
	 * @param <Array> $headers - output file header
	 * @param <Array> $entries - outfput file data
	 */
	function output($request, $headers, $entries)
	{
		$moduleName = $request->get('source_module');
		$fileName = str_replace(' ', '_', decode_html(vtranslate($moduleName, $moduleName))) . '.csv';
		$exportType = $this->getExportContentType($request);

		header("Content-Disposition: attachment; filename=\"$fileName\"");
		header("Content-Type: $exportType; charset=UTF-8");
		header("Expires: Mon, 31 Dec 2000 00:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: post-check=0, pre-check=0", false);

		# Start the ouput
		$output = fopen('php://output', 'w');
		fputcsv($output, $headers);

		foreach ($entries as $row) {
			fputcsv($output, $row);
		}
	}

	private $picklistValues;
	private $fieldArray;
	private $fieldDataTypeCache = [];

	/**
	 * this function takes in an array of values for an user and sanitizes it for export
	 * Requires modification after adding a new field type
	 * @param array $arr - the array of values
	 */
	function sanitizeValues($arr)
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$roleid = $currentUser->get('roleid');
		if (empty($this->fieldArray)) {
			$this->fieldArray = $this->moduleFieldInstances;
			foreach ($this->fieldArray as $fieldName => $fieldObj) {
				//In database we have same column name in two tables. - inventory modules only
				if ($fieldObj->get('table') == 'vtiger_inventoryproductrel' && ($fieldName == 'discount_amount' || $fieldName == 'discount_percent')) {
					//TODO To be removed together with the old inventory module
					$fieldName = 'item_' . $fieldName;
					$this->fieldArray[$fieldName] = $fieldObj;
				} else {
					$columnName = $fieldObj->get('column');
					$this->fieldArray[$columnName] = $fieldObj;
				}
			}
		}
		$moduleName = $this->moduleInstance->getName();
		foreach ($arr as $fieldName => &$value) {
			if (isset($this->fieldArray[$fieldName])) {
				$fieldInfo = $this->fieldArray[$fieldName];
			} else {
				unset($arr[$fieldName]);
				continue;
			}
			$value = trim(decode_html($value), "\"");
			$uitype = $fieldInfo->get('uitype');
			$fieldname = $fieldInfo->get('name');

			if (!$this->fieldDataTypeCache[$fieldName]) {
				$this->fieldDataTypeCache[$fieldName] = $fieldInfo->getFieldDataType();
			}
			$type = $this->fieldDataTypeCache[$fieldName];

			if ($fieldname != 'hdnTaxType' && ($uitype == 15 || $uitype == 16 || $uitype == 33)) {
				if (empty($this->picklistValues[$fieldname])) {
					$this->picklistValues[$fieldname] = $this->fieldArray[$fieldname]->getPicklistValues();
				}
				// If the value being exported is accessible to current user
				// or the picklist is multiselect type.
				if ($uitype == 33 || $uitype == 16 || array_key_exists($value, $this->picklistValues[$fieldname])) {
					// NOTE: multipicklist (uitype=33) values will be concatenated with |# delim
					$value = trim($value);
				} else {
					$value = '';
				}
			} elseif ($uitype == 52 || $type == 'owner') {
				$value = Vtiger_Util_Helper::getOwnerName($value);
			} elseif ($type == 'reference') {
				$value = trim($value);
				if (!empty($value)) {
					$recordModule = Vtiger_Functions::getCRMRecordType($value);
					$displayValueArray = Vtiger_Functions::computeCRMRecordLabels($recordModule, $value);
					if (!empty($displayValueArray)) {
						foreach ($displayValueArray as $k => $v) {
							$displayValue = $v;
						}
					}
					if (!empty($recordModule) && !empty($displayValue)) {
						$value = $recordModule . '::::' . $displayValue;
					} else {
						$value = '';
					}
				} else {
					$value = '';
				}
			} else if (in_array($uitype, [302])) {
				$value = $fieldInfo->getDisplayValue($value);
			}

			if ($moduleName == 'Documents' && $fieldname == 'description') {
				$value = strip_tags($value);
				$value = str_replace('&nbsp;', '', $value);
				array_push($new_arr, $value);
			}
		}
		return $arr;
	}

	function sanitizeInventoryValues($inventoryRow, $inventoryFields)
	{
		$inventoryEntries = [];
		foreach ($inventoryFields as $field) {
			$value = $inventoryRow[$field->getColumnName()];

			if (in_array($field->getName(), ['Name', 'Reference'])) {
				$value = trim($value);
				if (!empty($value)) {
					$recordModule = Vtiger_Functions::getCRMRecordType($value);
					$displayValueArray = Vtiger_Functions::computeCRMRecordLabels($recordModule, $value);
					if (!empty($displayValueArray)) {
						foreach ($displayValueArray as $k => $v) {
							$displayValue = $v;
						}
					}
					if (!empty($recordModule) && !empty($displayValue)) {
						$value = $recordModule . '::::' . $displayValue;
					} else {
						$value = '';
					}
				} else {
					$value = '';
				}
			} else {
				$value = $field->getDisplayValue($value);
			}
			$inventoryEntries['inv_' . $field->getColumnName()] = $value;
		}
		return $inventoryEntries;
	}
}
