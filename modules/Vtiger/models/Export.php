<?php

/**
 * Export Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Vtiger_Export_Model extends \App\Base
{
	/**
	 * Module model.
	 *
	 * @var Vtiger_Module_Model
	 */
	protected $moduleInstance;
	protected $focus;
	private $picklistValues;
	private $fieldArray;
	private $fieldDataTypeCache = [];
	protected $moduleName;
	protected $recordsListFromRequest = [];
	/**
	 * Query options.
	 *
	 * @var array
	 */
	protected $queryOptions;
	/**
	 * The type of exported file.
	 *
	 * @var string
	 */
	protected $exportType = 'csv';
	/**
	 * File extension.
	 *
	 * @var string
	 */
	protected $fileExtension = 'csv';

	/**
	 * Get supported file formats.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getSupportedFileFormats(string $moduleName): array
	{
		return AppConfig::module($moduleName, 'EXPORT_SUPPORTED_FILE_FORMATS') ?? ['LBL_CSV' => 'csv', 'LBL_XML' => 'xml'];
	}

	/**
	 * Get instance.
	 *
	 * @return \self
	 */
	public static function getInstance(string $moduleName, string $exportType = 'csv')
	{
		if ($exportType === 'csv' || empty($exportType)) {
			$componentName = 'Export';
		} else {
			$componentName = 'ExportTo' . ucfirst($exportType);
		}
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', $componentName, $moduleName);
		return new $modelClassName();
	}

	/**
	 * Get instance from request.
	 *
	 * @return \self
	 */
	public static function getInstanceFromRequest(\App\Request $request)
	{
		$module = $request->getByType('source_module', 'Alnum');
		if (empty($module)) {
			$module = $request->getModule();
		}
		$exportModel = static::getInstance($module, $request->getByType('export_type', 'Alnum'));
		$exportModel->initializeFromRequest($request);
		return $exportModel;
	}

	/**
	 * Initialize from request.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function initializeFromRequest(\App\Request $request)
	{
		$module = $request->getByType('source_module', 2);
		if (!empty($module)) {
			$this->moduleName = $module;
			$this->moduleInstance = Vtiger_Module_Model::getInstance($module);
			$this->moduleFieldInstances = $this->moduleInstance->getFields();
			$this->focus = CRMEntity::getInstance($module);
		}
		if (!$request->isEmpty('export_type')) {
			$this->exportType = $request->getByType('export_type');
		}
		if (!$request->isEmpty('viewname', true)) {
			$this->queryOptions['viewname'] = $request->getByType('viewname', 'Alnum');
		}
		$this->queryOptions['entityState'] = $request->getByType('entityState');
		$this->queryOptions['page'] = $request->getInteger('page');
		$this->queryOptions['mode'] = $request->getMode();
		$this->queryOptions['excluded_ids'] = $request->getArray('excluded_ids', 'Alnum');
	}

	/**
	 * Function exports the data based on the mode.
	 */
	public function exportData()
	{
		$module = $this->moduleName;
		$query = $this->getExportQuery();
		$headers = [];
		$exportBlockName = \App\Config::component('Export', 'BLOCK_NAME');
		//Query generator set this when generating the query
		if (!empty($this->accessibleFields)) {
			foreach ($this->accessibleFields as $fieldName) {
				if (!empty($this->moduleFieldInstances[$fieldName])) {
					$fieldModel = $this->moduleFieldInstances[$fieldName];
					// Check added as querygenerator is not checking this for admin users
					if ($fieldModel && $fieldModel->isExportTable()) { // export headers for mandatory fields
						$header = \App\Language::translate(html_entity_decode($fieldModel->get('label'), ENT_QUOTES), $module);
						if ($exportBlockName) {
							$header = App\Language::translate(html_entity_decode($fieldModel->getBlockName(), ENT_QUOTES), $module) . '::' . $header;
						}
						$headers[] = $header;
					}
				}
			}
		} else {
			foreach ($this->moduleFieldInstances as $fieldModel) {
				$header = \App\Language::translate(html_entity_decode($fieldModel->get('label'), ENT_QUOTES), $module);
				if ($exportBlockName) {
					$header = App\Language::translate(html_entity_decode($fieldModel->getBlockName(), ENT_QUOTES), $module) . '::' . $header;
				}
				$headers[] = $header;
			}
		}
		$isInventory = $this->moduleInstance->isInventory();
		if ($isInventory) {
			//Get inventory headers
			$inventoryModel = Vtiger_Inventory_Model::getInstance($module);
			$inventoryFields = $inventoryModel->getFields();
			$headers[] = 'Inventory::recordIteration';
			foreach ($inventoryFields as &$field) {
				$headers[] = 'Inventory::' . \App\Language::translate(html_entity_decode($field->get('label'), ENT_QUOTES), $module);
				foreach ($field->getCustomColumn() as $columnName => $dbType) {
					$headers[] = 'Inventory::' . $columnName;
				}
			}
			$table = $inventoryModel->getDataTableName();
		}
		$entries = [];
		$dataReader = $query->createCommand()->query();
		$i = 0;
		while ($row = $dataReader->read()) {
			$sanitizedRow = $this->sanitizeValues($row);
			if ($isInventory) {
				$sanitizedRow[] = $i++;
				$rows = (new \App\Db\Query())->from($table)->where(['crmid' => $row['id']])->orderBy('seq')->all();
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
		$dataReader->close();
		$this->output($headers, $entries);
	}

	/**
	 * Function that generates Export Query based on the mode.
	 *
	 * @throws \Exception
	 *
	 * @return \App\Db\Query
	 */
	public function getExportQuery()
	{
		$queryGenerator = new \App\QueryGenerator($this->moduleName);
		if (!empty($this->queryOptions['viewname'])) {
			$queryGenerator->initForCustomViewById($this->queryOptions['viewname']);
		}
		$fieldInstances = $this->moduleFieldInstances;
		$fields[] = 'id';
		foreach ($fieldInstances as &$fieldModel) {
			// Check added as querygenerator is not checking this for admin users
			if ($fieldModel->isViewEnabled() || $fieldModel->isMandatory()) {  // also export mandatory fields
				$fields[] = $fieldModel->getName();
			}
		}
		$queryGenerator->setFields($fields);
		$queryGenerator->setStateCondition($this->queryOptions['entityState']);
		$query = $queryGenerator->createQuery();
		$this->accessibleFields = $queryGenerator->getFields();
		switch ($this->queryOptions['mode']) {
			case 'ExportAllData':
				$query->limit(AppConfig::performance('MAX_NUMBER_EXPORT_RECORDS'));
				break;
			case 'ExportCurrentPage':
				$pagingModel = new Vtiger_Paging_Model();
				$limit = $pagingModel->getPageLimit();
				$currentPage = $this->queryOptions['page'];
				if (empty($currentPage)) {
					$currentPage = 1;
				}
				$currentPageStart = ($currentPage - 1) * $limit;
				if ($currentPageStart < 0) {
					$currentPageStart = 0;
				}
				$query->limit($limit)->offset($currentPageStart);
				break;
			case 'ExportSelectedRecords':
				$idList = $this->recordsListFromRequest;
				$baseTable = $this->moduleInstance->get('basetable');
				$baseTableColumnId = $this->moduleInstance->get('basetableid');
				if (!empty($idList)) {
					if (!empty($baseTable) && !empty($baseTableColumnId)) {
						$query->andWhere(['in', "$baseTable.$baseTableColumnId", $idList]);
					}
				} else {
					$query->andWhere(['not in', "$baseTable.$baseTableColumnId", $this->queryOptions['excluded_ids']]);
				}
				$query->limit(AppConfig::performance('MAX_NUMBER_EXPORT_RECORDS'));
				break;
			default:
				break;
		}
		return $query;
	}

	/**
	 * Function returns the export type - This can be extended to support different file exports.
	 *
	 * @return string
	 */
	public function getExportContentType(): string
	{
		return "text/{$this->exportType}";
	}

	/**
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public function getFileName(): string
	{
		return str_replace(' ', '_', \App\Purifier::decodeHtml(\App\Language::translate($this->moduleName, $this->moduleName))) .
			".{$this->fileExtension}";
	}

	/**
	 * Function that create the exported file.
	 *
	 * @param array $headers - output file header
	 * @param array $entries - outfput file data
	 */
	public function output($headers, $entries)
	{
		$output = fopen('php://output', 'w');
		fputcsv($output, $headers);
		foreach ($entries as $row) {
			fputcsv($output, $row);
		}
		fclose($output);
	}

	/**
	 * Send HTTP Header.
	 */
	public function sendHttpHeader()
	{
		header("content-disposition: attachment; filename=\"{$this->getFileName()}\"");
		header("content-type: {$this->getExportContentType()}; charset=UTF-8");
		header('expires: Mon, 31 Dec 2000 00:00:00 GMT');
		header('last-modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('cache-control: post-check=0, pre-check=0', false);
	}

	/**
	 * this function takes in an array of values for an user and sanitizes it for export
	 * Requires modification after adding a new field type.
	 *
	 * @param array $arr - the array of values
	 */
	public function sanitizeValues($arr)
	{
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
		$recordId = $arr[$this->focus->table_index] ?? '';
		$module = $this->moduleInstance->getName();
		foreach ($arr as $fieldName => &$value) {
			if (isset($this->fieldArray[$fieldName])) {
				$fieldInfo = $this->fieldArray[$fieldName];
			} else {
				unset($arr[$fieldName]);
				continue;
			}
			$value = trim(App\Purifier::decodeHtml($value), '"');
			$uitype = $fieldInfo->get('uitype');
			$fieldname = $fieldInfo->get('name');
			if (empty($this->fieldDataTypeCache[$fieldName])) {
				$this->fieldDataTypeCache[$fieldName] = $fieldInfo->getFieldDataType();
			}
			$type = $this->fieldDataTypeCache[$fieldName];
			if ($uitype === 15 || $uitype === 16 || $uitype === 33) {
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
			} elseif ($uitype === 99) {
				$value = '';
			} elseif ($uitype === 52 || $type === 'owner') {
				$value = \App\Fields\Owner::getLabel($value);
			} elseif ($uitype === 120) {
				$values = [];
				foreach (\App\Fields\SharedOwner::getById($recordId) as $owner) {
					$values[] = \App\Fields\Owner::getLabel($owner);
				}
				$value = implode(',', $values);
			} elseif ($type === 'reference') {
				$value = trim($value);
				if (!empty($value)) {
					$recordModule = \App\Record::getType($value);
					$displayValueArray = \App\Record::computeLabels($recordModule, $value);
					if (!empty($displayValueArray)) {
						foreach ($displayValueArray as $v) {
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
			} elseif (in_array($uitype, [302, 309])) {
				$parts = explode(',', trim($value, ', '));
				$values = \App\Fields\Tree::getValuesById((int) $fieldInfo->getFieldParams());
				foreach ($parts as &$part) {
					foreach ($values as $id => $treeRow) {
						if ($part === $id) {
							$part = $treeRow['name'];
						}
					}
				}
				$value = implode(' |##| ', $parts);
			}
			if ($module === 'Documents' && $fieldname === 'description') {
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
			if (in_array($field->getType(), ['Name', 'Reference'])) {
				$value = trim($value);
				if (!empty($value)) {
					$recordModule = \App\Record::getType($value);
					$displayValue = \App\Record::getLabel($value);
					if (!empty($recordModule) && !empty($displayValue)) {
						$value = $recordModule . '::::' . $displayValue;
					} else {
						$value = '';
					}
				} else {
					$value = '';
				}
			} elseif ($field->getType() === 'Currency') {
				$value = $field->getDisplayValue($value);
			} else {
				$value;
			}
			$inventoryEntries['inv_' . $columnName] = $value;
			foreach ($field->getCustomColumn() as $customColumnName => $dbType) {
				$valueParam = $inventoryRow[$customColumnName];
				if ($customColumnName === 'currencyparam') {
					$field = $inventoryFields['currency'];
					$valueData = $field->getCurrencyParam([], $valueParam);
					if (is_array($valueData)) {
						$valueNewData = [];
						foreach ($valueData as $currencyId => $data) {
							$currencyName = \App\Fields\Currency::getById($currencyId)['currency_name'];
							$valueNewData[$currencyName] = $data;
						}
						$valueParam = \App\Json::encode($valueNewData);
					}
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
