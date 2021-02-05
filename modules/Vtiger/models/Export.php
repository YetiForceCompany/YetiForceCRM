<?php

/**
 * Export Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Export_Model extends \App\Base
{
	/**
	 * Module model.
	 *
	 * @var Vtiger_Module_Model
	 */
	protected $moduleInstance;
	/**
	 * Field model instance.
	 *
	 * @var Vtiger_Field_Model[]
	 */
	protected $moduleFieldInstances;
	protected $focus;
	private $picklistValues;
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
		return App\Config::module($moduleName, 'EXPORT_SUPPORTED_FILE_FORMATS') ?? [
			'LBL_CSV' => 'csv',
			'LBL_XML' => 'xml',
			'LBL_XLS' => 'xls',
			'LBL_XLSX' => 'xlsx',
			'LBL_ODS' => 'ods'
		];
	}

	/**
	 * Get instance.
	 *
	 * @param string $moduleName
	 * @param string $exportType
	 *
	 * @return \self
	 */
	public static function getInstance(string $moduleName, string $exportType = 'csv')
	{
		if ('csv' === $exportType || empty($exportType)) {
			$componentName = 'Export';
		} elseif ('xls' === $exportType || 'xlsx' === $exportType || 'ods' === $exportType) {
			$componentName = 'ExportToSpreadsheet';
		} else {
			$componentName = 'ExportTo' . ucfirst($exportType);
		}
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', $componentName, $moduleName);
		return new $modelClassName();
	}

	/**
	 * Get instance from request.
	 *
	 * @param App\Request $request
	 *
	 * @return \self
	 */
	public static function getInstanceFromRequest(App\Request $request)
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
	public function initializeFromRequest(App\Request $request)
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
		if (!$request->isEmpty('entityState')) {
			$this->queryOptions['entityState'] = $request->getByType('entityState');
		}
		$this->queryOptions['entityState'] = $request->getByType('entityState');
		$this->queryOptions['page'] = $request->getInteger('page');
		$this->queryOptions['mode'] = $request->getMode();
		$this->queryOptions['excluded_ids'] = $request->getArray('excluded_ids', 'Alnum');
	}

	/**
	 * Get file headers.
	 *
	 * @return array
	 */
	public function getHeaders(): array
	{
		$headers = [];
		$exportBlockName = \App\Config::component('Export', 'BLOCK_NAME');
		foreach ($this->accessibleFields as $fieldName) {
			if (!empty($this->moduleFieldInstances[$fieldName])) {
				$fieldModel = $this->moduleFieldInstances[$fieldName];
				// Check added as querygenerator is not checking this for admin users
					if ($fieldModel) { // export headers for mandatory fields
						$header = \App\Language::translate(App\Purifier::decodeHtml($fieldModel->get('label')), $this->moduleName);
						if ($exportBlockName) {
							$header = App\Language::translate(App\Purifier::decodeHtml($fieldModel->getBlockName()), $this->moduleName) . '::' . $header;
						}
						$headers[] = $header;
					}
			}
		}
		if ($this->moduleInstance->isInventory()) {
			//Get inventory headers
			$inventoryModel = Vtiger_Inventory_Model::getInstance($this->moduleName);
			$inventoryFields = $inventoryModel->getFields();
			$headers[] = 'Inventory::recordIteration';
			foreach ($inventoryFields as $field) {
				$headers[] = 'Inventory::' . \App\Language::translate(App\Purifier::decodeHtml($field->get('label')), $this->moduleName);
				foreach ($field->getCustomColumn() as $columnName => $dbType) {
					$headers[] = 'Inventory::' . $columnName;
				}
			}
		}
		return $headers;
	}

	/**
	 * Function exports the data based on the mode.
	 */
	public function exportData()
	{
		$query = $this->getExportQuery();
		$headers = $this->getHeaders();
		$isInventory = $this->moduleInstance->isInventory();
		if ($isInventory) {
			$inventoryModel = Vtiger_Inventory_Model::getInstance($this->moduleName);
			$inventoryFields = $inventoryModel->getFields();
			$inventoryTable = $inventoryModel->getDataTableName();
		}
		$entries = [];
		$dataReader = $query->createCommand()->query();
		$i = 0;
		while ($row = $dataReader->read()) {
			$sanitizedRow = $this->sanitizeValues($row);
			if ($isInventory) {
				$sanitizedRow[] = $i++;
				$rows = (new \App\Db\Query())->from($inventoryTable)->where(['crmid' => $row['id']])->orderBy('seq')->all();
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
			if ($fieldModel->isExportTable() && ($fieldModel->isViewEnabled() || $fieldModel->isMandatory())) {  // also export mandatory fields
				$fields[] = $fieldModel->getName();
			}
		}
		$queryGenerator->setFields($fields);
		if (!empty($this->queryOptions['entityState'])) {
			$queryGenerator->setStateCondition($this->queryOptions['entityState']);
		}
		$query = $queryGenerator->createQuery();
		$this->accessibleFields = $queryGenerator->getFields();
		switch ($this->queryOptions['mode']) {
			case 'ExportAllData':
				$query->limit(App\Config::performance('MAX_NUMBER_EXPORT_RECORDS'));
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
				$query->limit(App\Config::performance('MAX_NUMBER_EXPORT_RECORDS'));
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
		return \App\Fields\File::getMimeContentType($this->getFileName());
	}

	/**
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public function getFileName(): string
	{
		return str_replace(' ', '_', \App\Purifier::decodeHtml(\App\Language::translate($this->moduleName, $this->moduleName))) . ".{$this->fileExtension}";
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
		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
			header('pragma: public');
		}
		header('cache-control: post-check=0, pre-check=0', false);
	}

	/**
	 * this function takes in an array of values for an user and sanitizes it for export
	 * Requires modification after adding a new field type.
	 *
	 * @param array $arr - the array of values
	 */
	public function sanitizeValues(array $arr)
	{
		$recordId = (int) ($arr['id'] ?? 0);
		$module = $this->moduleInstance->getName();
		foreach ($arr as $fieldName => &$value) {
			if (isset($this->moduleFieldInstances[$fieldName])) {
				$fieldModel = $this->moduleFieldInstances[$fieldName];
			} else {
				unset($arr[$fieldName]);
				continue;
			}
			$value = $fieldModel->getUITypeModel()->getValueToExport($value, $recordId);
			$uitype = $fieldModel->get('uitype');
			$fieldname = $fieldModel->get('name');
			if (empty($this->fieldDataTypeCache[$fieldName])) {
				$this->fieldDataTypeCache[$fieldName] = $fieldModel->getFieldDataType();
			}
			$type = $this->fieldDataTypeCache[$fieldName];
			if (15 === $uitype || 16 === $uitype || 33 === $uitype) {
				if (empty($this->picklistValues[$fieldname])) {
					$this->picklistValues[$fieldname] = $this->moduleFieldInstances[$fieldname]->getPicklistValues();
				}
				// If the value being exported is accessible to current user
				// or the picklist is multiselect type.
				if (33 === $uitype || 16 === $uitype || \array_key_exists($value, $this->picklistValues[$fieldname])) {
					// NOTE: multipicklist (uitype=33) values will be concatenated with |# delim
					$value = trim($value);
				} else {
					$value = '';
				}
			} elseif (99 === $uitype) {
				$value = '';
			} elseif (52 === $uitype || 'owner' === $type) {
				$value = \App\Fields\Owner::getLabel($value);
			} elseif ($fieldModel->isReferenceField()) {
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
			} elseif (\in_array($uitype, [302, 309])) {
				$parts = explode(',', trim($value, ', '));
				$values = \App\Fields\Tree::getValuesById((int) $fieldModel->getFieldParams());
				foreach ($parts as &$part) {
					foreach ($values as $id => $treeRow) {
						if ($part === $id) {
							$part = $treeRow['name'];
						}
					}
				}
				$value = implode(' |##| ', $parts);
			}
			if ('Documents' === $module && 'description' === $fieldname) {
				$value = strip_tags($value);
				$value = str_replace('&nbsp;', '', $value);
				array_push($new_arr, $value);
			}
		}
		return $arr;
	}

	/**
	 * Sanitize inventory values.
	 *
	 * @param array $inventoryRow
	 * @param array $inventoryFields
	 *
	 * @return array
	 */
	public function sanitizeInventoryValues(array $inventoryRow, array $inventoryFields): array
	{
		$inventoryEntries = [];
		foreach ($inventoryFields as $columnName => $field) {
			$value = $inventoryRow[$columnName];
			if (\in_array($field->getType(), ['Name', 'Reference'])) {
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
			} elseif ('Currency' === $field->getType()) {
				$value = $field->getDisplayValue($value);
			} else {
				$value;
			}
			$inventoryEntries['inv_' . $columnName] = $value;
			foreach ($field->getCustomColumn() as $customColumnName => $dbType) {
				$valueParam = $inventoryRow[$customColumnName];
				if ('currencyparam' === $customColumnName) {
					$field = $inventoryFields['currency'];
					$valueData = $field->getCurrencyParam([], $valueParam);
					if (\is_array($valueData)) {
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
