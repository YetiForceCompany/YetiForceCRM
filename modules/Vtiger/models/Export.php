<?php

/**
 * Export Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Export_Model extends Vtiger_Base_Model
{

	protected $moduleInstance;
	protected $focus;
	private $picklistValues;
	private $fieldArray;
	private $fieldDataTypeCache = [];
	protected $moduleName;
	protected $recordsListFromRequest = [];

	public static function getInstanceFromRequest(Vtiger_Request $request)
	{
		$moduleName = $request->get('source_module');
		if (empty($moduleName)) {
			$moduleName = $request->getModule();
		}
		$componentName = 'Export';
		if ('xml' === $request->get('export_type')) {
			$componentName = 'ExportToXml';
		}
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', $componentName, $moduleName);
		$exportModel = new $modelClassName();
		$exportModel->initialize($request);
		return $exportModel;
	}

	public function initialize(Vtiger_Request $request)
	{
		$moduleName = $request->get('source_module');
		if (!empty($moduleName)) {
			$this->moduleName = $moduleName;
			$this->moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
			$this->moduleFieldInstances = $this->moduleInstance->getFields();
			$this->focus = CRMEntity::getInstance($moduleName);
		}
	}

	/**
	 * Function exports the data based on the mode
	 * @param Vtiger_Request $request
	 */
	public function exportData(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$moduleName = $request->get('source_module');
		$query = $this->getExportQuery($request);

		$headers = [];
		$exportBlockName = \AppConfig::module('Export', 'BLOCK_NAME');
		//Query generator set this when generating the query
		if (!empty($this->accessibleFields)) {
			foreach ($this->accessibleFields as &$fieldName) {
				$fieldModel = $this->moduleFieldInstances[$fieldName];
				// Check added as querygenerator is not checking this for admin users
				if ($fieldModel && $fieldModel->isViewEnabled()) {
					$header = \App\Language::translate(html_entity_decode($fieldModel->get('label'), ENT_QUOTES), $moduleName);
					if ($exportBlockName) {
						$header = App\Language::translate(html_entity_decode($fieldModel->getBlockName(), ENT_QUOTES), $moduleName) . '::' . $header;
					}
					$headers[] = $header;
				}
			}
		} else {
			foreach ($this->moduleFieldInstances as &$fieldModel) {
				$header = \App\Language::translate(html_entity_decode($fieldModel->get('label'), ENT_QUOTES), $moduleName);
				if ($exportBlockName) {
					$header = App\Language::translate(html_entity_decode($fieldModel->getBlockName(), ENT_QUOTES), $moduleName) . '::' . $header;
				}
				$headers[] = $header;
			}
		}

		$isInventory = $this->moduleInstance->isInventory();
		if ($isInventory) {
			//Get inventory headers
			$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($moduleName);
			$inventoryFields = $inventoryFieldModel->getFields();
			$headers[] = 'Inventory::recordIteration';
			foreach ($inventoryFields as &$field) {
				$headers[] = 'Inventory::' . \App\Language::translate(html_entity_decode($field->get('label'), ENT_QUOTES), $moduleName);
				foreach ($field->getCustomColumn() as $columnName => $dbType) {
					$headers[] = 'Inventory::' . $columnName;
				}
			}
			$table = $inventoryFieldModel->getTableName('data');
		}

		$entries = [];
		$dataReader = $query->createCommand()->query();
		$i = 0;
		while ($row = $dataReader->read()) {
			$sanitizedRow = $this->sanitizeValues($row);
			if ($isInventory) {
				$sanitizedRow[] = $i++;
				$rows = (new \App\Db\Query())->from($table)->where(['id' => $row['id']])->orderBy('seq')->all();
				if ($rows) {
					foreach ($rows as &$row) {
						$sanitizedInventoryRow = $this->sanitizeInventoryValues($row, $inventoryFields);
						$entries[] = array_merge($sanitizedRow, $sanitizedInventoryRow);
					}
				} else {
					$entries[] = $sanitizedRow;
				}
			} else {
				$entries[] = $sanitizedRow;
			}
		}
		$this->output($request, $headers, $entries);
	}

	/**
	 * Function that generates Export Query based on the mode
	 * @param Vtiger_Request $request
	 * @return string export query
	 */
	public function getExportQuery(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		$cvId = $request->get('viewname');
		$queryGenerator = new \App\QueryGenerator($request->get('source_module'));
		if (!empty($cvId)) {
			$queryGenerator->initForCustomViewById($cvId);
		}
		$fieldInstances = $this->moduleFieldInstances;

		$accessiblePresenceValue = [0, 2];
		$fields[] = 'id';
		foreach ($fieldInstances as &$fieldModel) {
			// Check added as querygenerator is not checking this for admin users
			if ($fieldModel->isViewEnabled()) {
				$fields[] = $fieldModel->getName();
			}
		}
		$queryGenerator->setFields($fields);
		$query = $queryGenerator->createQuery();
		$this->accessibleFields = $queryGenerator->getFields();
		switch ($mode) {
			case 'ExportAllData' :
				$query->limit(AppConfig::performance('MAX_NUMBER_EXPORT_RECORDS'));
				break;

			case 'ExportCurrentPage' :
				$pagingModel = new Vtiger_Paging_Model();
				$limit = $pagingModel->getPageLimit();
				$currentPage = $request->get('page');
				if (empty($currentPage)) {
					$currentPage = 1;
				}
				$currentPageStart = ($currentPage - 1) * $limit;
				if ($currentPageStart < 0) {
					$currentPageStart = 0;
				}
				$query->limit($limit)->offset($currentPageStart);
				break;

			case 'ExportSelectedRecords' :
				$idList = $this->recordsListFromRequest;
				$baseTable = $this->moduleInstance->get('basetable');
				$baseTableColumnId = $this->moduleInstance->get('basetableid');
				if (!empty($idList)) {
					if (!empty($baseTable) && !empty($baseTableColumnId)) {
						$query->andWhere(['in', "$baseTable.$baseTableColumnId", $idList]);
					}
				} else {
					$query->andWhere(['not in', "$baseTable.$baseTableColumnId", $request->get('excluded_ids')]);
				}
				$query->limit(AppConfig::performance('MAX_NUMBER_EXPORT_RECORDS'));
				break;
		}
		return $query;
	}

	/**
	 * Function returns the export type - This can be extended to support different file exports
	 * @param Vtiger_Request $request
	 * @return string
	 */
	public function getExportContentType(Vtiger_Request $request)
	{
		$type = $request->get('export_type');
		if (empty($type)) {
			return 'text/csv';
		}
	}

	/**
	 * Function that create the exported file
	 * @param Vtiger_Request $request
	 * @param array $headers - output file header
	 * @param array $entries - outfput file data
	 */
	public function output($request, $headers, $entries)
	{
		$moduleName = $request->get('source_module');
		$fileName = str_replace(' ', '_', decode_html(\App\Language::translate($moduleName, $moduleName))) . '.csv';
		$exportType = $this->getExportContentType($request);

		header("Content-Disposition: attachment; filename=\"$fileName\"");
		header("Content-Type: $exportType; charset=UTF-8");
		header('Expires: Mon, 31 Dec 2000 00:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: post-check=0, pre-check=0', false);

		# Start the ouput
		$output = fopen('php://output', 'w');
		fputcsv($output, $headers);
		foreach ($entries as $row) {
			fputcsv($output, $row);
		}
		fclose($output);
	}

	/**
	 * this function takes in an array of values for an user and sanitizes it for export
	 * Requires modification after adding a new field type
	 * @param array $arr - the array of values
	 */
	public function sanitizeValues($arr)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$roleid = $currentUser->get('roleid');
		if (empty($this->fieldArray)) {
			$this->fieldArray = $this->moduleFieldInstances;
			foreach ($this->fieldArray as $fieldName => $fieldObj) {
				//In database we have same column name in two tables. - inventory modules only
				if ($fieldObj->get('table') == 'vtiger_inventoryproductrel' && ($fieldName == 'discount_amount' || $fieldName == 'discount_percent')) {
					$fieldName = 'item_' . $fieldName;
					$this->fieldArray[$fieldName] = $fieldObj;
				} else {
					$columnName = $fieldObj->get('column');
					$this->fieldArray[$columnName] = $fieldObj;
				}
			}
		}
		$recordId = $arr[$this->focus->table_index];
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
			if ($fieldname !== 'hdnTaxType' && ($uitype === 15 || $uitype === 16 || $uitype === 33)) {
				if (empty($this->picklistValues[$fieldname])) {
					$this->picklistValues[$fieldname] = $this->fieldArray[$fieldname]->getPicklistValues();
				}
				// If the value being exported is accessible to current user
				// or the picklist is multiselect type.
				if ($uitype === 33 || $uitype === 16 || array_key_exists($value, $this->picklistValues[$fieldname])) {
					// NOTE: multipicklist (uitype=33) values will be concatenated with |# delim
					$value = trim($value);
				} else {
					$value = '';
				}
			} elseif ($uitype === 52 || $type === 'owner') {
				$value = Vtiger_Util_Helper::getOwnerName($value);
			} elseif ($uitype === 120) {
				$uitypeInstance = new Vtiger_SharedOwner_UIType;
				$owners = $uitypeInstance->getEditViewDisplayValue([], $recordId);
				$values = [];
				foreach ($owners as $owner) {
					$values[] = Vtiger_Util_Helper::getOwnerName($owner);
				}
				$value = implode(',', $values);
			} elseif ($type === 'reference') {
				$value = trim($value);
				if (!empty($value)) {
					$recordModule = \vtlib\Functions::getCRMRecordType($value);
					$displayValueArray = \App\Record::computeLabels($recordModule, $value);
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
			if ($moduleName === 'Documents' && $fieldname === 'description') {
				$value = strip_tags($value);
				$value = str_replace('&nbsp;', '', $value);
				array_push($new_arr, $value);
			}
		}
		return $arr;
	}

	public function sanitizeInventoryValues($inventoryRow, $inventoryFields)
	{
		$inventoryEntries = [];
		foreach ($inventoryFields as $columnName => $field) {
			$value = $inventoryRow[$columnName];
			if (in_array($field->getName(), ['Name', 'Reference'])) {
				$value = trim($value);
				if (!empty($value)) {
					$recordModule = \vtlib\Functions::getCRMRecordType($value);
					$displayValue = \App\Record::getLabel($value);
					if (!empty($recordModule) && !empty($displayValue)) {
						$value = $recordModule . '::::' . $displayValue;
					} else {
						$value = '';
					}
				} else {
					$value = '';
				}
			} elseif ($field->getName() === 'Currency') {
				$value = $field->getDisplayValue($value);
			} else {
				$value;
			}
			$inventoryEntries['inv_' . $columnName] = $value;
			foreach ($field->getCustomColumn() as $customColumnName => $dbType) {
				$valueParam = $inventoryRow[$customColumnName];
				switch ($customColumnName) {
					case 'currencyparam':
						$field = $inventoryFields['currency'];
						$valueData = $field->getCurrencyParam([], $valueParam);
						$valueNewData = [];
						foreach ($valueData as $currencyId => &$data) {
							$currencyName = vtlib\Functions::getCurrencyName($currencyId, false);
							$data['value'] = $currencyName;
							$valueNewData[$currencyName] = $data;
						}
						$valueParam = \App\Json::encode($valueNewData);
						break;
					default:
						break;
				}
				$inventoryEntries['inv_' . $customColumnName] = $valueParam;
			}
		}
		return $inventoryEntries;
	}

	public function getModuleName()
	{
		return $this->moduleName;
	}

	public function setRecordList($listId)
	{
		return $this->recordsListFromRequest = $listId;
	}
}
