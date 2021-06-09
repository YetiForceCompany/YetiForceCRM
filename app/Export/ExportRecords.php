<?php
/**
 * Abstract base view controller file.
 *
 * @package   Export
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

namespace App\Export;

/**
 * Abstract export records class.
 */
abstract class ExportRecords extends \App\Base
{
	/** @var string Module name */
	protected $moduleName;
	/** @var array Columns selected by user */
	protected $exportColumns = [];
	/** @var Vtiger_Module_Model Module model. */
	protected $moduleInstance;
	/** @var Vtiger_Field_Model[] Field model instance. */
	protected $moduleFieldInstances;
	/** @var CRMEntity Module class */
	protected $focus;
	/** @var array Picklist values */
	protected $picklistValues;
	/** @var array Cached field data types */
	protected $fieldDataTypeCache = [];
	/** @var array Field from related modules */
	protected $relatedModuleFields = [];
	/** @var int Record from list */
	protected $recordsListFromRequest = [];
	/** @var string File extension */
	protected $fileExtension = '';
	/** @var array Query options */
	protected $queryOptions;
	/** @var bool If is quick export */
	protected $quickExport = false;

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
			$componentName = 'ExportToCsv';
		} elseif ('xls' === $exportType || 'xlsx' === $exportType || 'ods' === $exportType) {
			$componentName = 'ExportToSpreadsheet';
		} else {
			$componentName = 'ExportTo' . ucfirst($exportType);
		}
		$modelClassName = \Vtiger_Loader::getComponentClassName('Model', $componentName, $moduleName);
		return new $modelClassName();
	}

	/**
	 * Get instance from request.
	 *
	 * @param App\Request $request
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
	 * Get supported file formats.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public static function getSupportedFileFormats(string $moduleName): array
	{
		return \App\Config::module($moduleName, 'EXPORT_SUPPORTED_FILE_FORMATS') ?? [
			'LBL_CSV' => 'csv',
			'LBL_XML' => 'xml',
			'LBL_XLS' => 'xls',
		];
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
		if (empty($module)) {
			$module = $request->getModule();
		}
		if (!empty($module)) {
			$this->moduleName = $module;
			$this->moduleInstance = \Vtiger_Module_Model::getInstance($module);
			$this->moduleFieldInstances = $this->moduleInstance->getFields();
			$this->focus = \CRMEntity::getInstance($module);
		}
		if ($request->has('exportColumns') && !$request->isEmpty('exportColumns')) {
			$this->exportColumns = $request->getArray('exportColumns', 'Text')[0];
		}
		if ($request->has('quickExport') && !$request->isEmpty('quickExport')) {
			$this->quickExport = true;
			$this->queryGeneratorForList = \Vtiger_Mass_Action::getQuery($request);
			$this->setRecordList(\Vtiger_Mass_Action::getRecordsListFromRequest($request));
		}
		if (!$request->isEmpty('export_type')) {
			$this->exportType = $request->getByType('export_type');
		}
		if (!$request->isEmpty('viewname', true)) {
			$this->queryOptions['viewname'] = $request->getByType('viewname', 'Alnum');
			$listViewModel = \Vtiger_ListView_Model::getInstance($this->moduleName, $this->queryOptions['viewname']);
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		if (!$request->isEmpty('entityState')) {
			$this->queryOptions['entityState'] = $request->getByType('entityState');
		}

		$this->queryOptions['page'] = $request->getInteger('page');
		$this->queryOptions['mode'] = $request->getMode();
		$this->queryOptions['excluded_ids'] = $request->getArray('excluded_ids', 'Alnum');
	}

	/**
	 * Function set id's of records from list.
	 *
	 * @param array $listId
	 *
	 * @return array
	 */
	public function setRecordList(array $listId): array
	{
		return $this->recordsListFromRequest = $listId;
	}

	/**
	 * Get file headers.
	 *
	 * @return array
	 */
	public function getHeaders(): array
	{
		if ($this->quickExport && isset($this->queryOptions['viewname'])) {
			if ($this->exportColumns) {
				$headers = $this->getHeadersSelectedByUser();
			} else {
				$headers = $this->getHeadersFromCurrentView();
			}
		} else {
			$headers = $this->getAllModuleFieldsAsHeaders();
		}

		return $headers;
	}

	/**
	 * Function returns headers for file selected by user.
	 *
	 * @return array
	 */
	public function getHeadersSelectedByUser(): array
	{
		$headers = [];
		foreach (explode(',', $this->exportColumns) as $index => $columnInfo) {
			$label = '';
			[$relatedFieldName, $relatedModule, $referenceField] = array_pad(explode(':', $columnInfo), 3, null);
			if ($referenceField) {
				$this->setReferenceField($relatedFieldName, $relatedModule, $referenceField);
			}
			if (isset($this->moduleFieldInstances[$relatedFieldName])) {
				$fieldModel = $this->moduleFieldInstances[$relatedFieldName];
				if ($fieldModel) {
					$label = $fieldModel->getFullLabelTranslation($this->moduleInstance);
					$headers[] = \App\Purifier::decodeHtml($label);
				}
			}
			if (isset($this->moduleFieldInstances[$referenceField])) {
				$fieldModel = $this->moduleFieldInstances[$referenceField];
				$label = $fieldModel->getFullLabelTranslation($this->moduleInstance);
				$relatedModuleInstance = \Vtiger_Module_Model::getInstance($relatedModule);
				if ($fieldFromReferenceModule = \Vtiger_Field_Model::getInstance($relatedFieldName, $relatedModuleInstance)) {
					$label .= ' - ' . $fieldFromReferenceModule->getFullLabelTranslation($relatedModuleInstance);
				}
				$headers[] = \App\Purifier::decodeHtml($label);
			}
		}
		return $headers;
	}

	/**
	 * Function returns headers specified in current filter.
	 *
	 * @return array
	 */
	public function getHeadersFromCurrentView(): array
	{
		$headers = [];
		foreach ($this->listViewHeaders as $fieldModel) {
			$label = $fieldModel->getFullLabelTranslation($this->moduleInstance);
			$headers[] = \App\Purifier::decodeHtml($label);
		}
		return $headers;
	}

	/**
	 * Function sets module reference field.
	 *
	 * @param string $relatedFieldName
	 * @param string $relatedModule
	 * @param string $referenceField
	 */
	public function setReferenceField(string $relatedFieldName, string $relatedModule, string $referenceField)
	{
		$relatedFields = \Vtiger_Module_Model::getInstance($relatedModule)->getFields();
		if (!isset($this->moduleFieldInstances[$referenceField], $relatedFields[$relatedFieldName])) {
			throw new \App\Exceptions\IllegalValue("ERR_FIELD_NOT_FOUND||{$relatedFieldName}||");
		}
		$referenceFieldName = $referenceField . $relatedModule . $relatedFieldName;
		$this->relatedModuleFields[$referenceFieldName] = $relatedFields[$relatedFieldName];
	}

	/**
	 * Function returns all module fields as headers.
	 *
	 * @return array
	 */
	public function getAllModuleFieldsAsHeaders(): array
	{
		$headers = [];
		$exportBlockName = \App\Config::component('Export', 'BLOCK_NAME');
		foreach ($this->accessibleFields as $fieldName) {
			if ($fieldHeader = $this->getHeaderLabelForField($fieldName, $this->moduleName, null, $exportBlockName)) {
				$headers[] = $fieldHeader;
			}
		}
		if ($this->moduleInstance->isInventory()) {
			$inventoryModel = \Vtiger_Inventory_Model::getInstance($this->moduleName);
			$inventoryFields = $inventoryModel->getFields();
			$headers[] = 'Inventory::recordIteration';
			foreach ($inventoryFields as $field) {
				$headers[] = 'Inventory::' . \App\Language::translate(\App\Purifier::decodeHtml($field->get('label')), $this->moduleName);
				foreach ($field->getCustomColumn() as $columnName => $dbType) {
					$headers[] = 'Inventory::' . $columnName;
				}
			}
		}
		return $headers;
	}

	/**
	 * Function returns heder label for the field.
	 *
	 * @param string      $fieldName
	 * @param string      $moduleName
	 * @param string|null $referenceField
	 * @param bool        $exportBlockName
	 *
	 * @return string
	 */
	public function getHeaderLabelForField(string $fieldName, string $moduleName, ?string $referenceField, bool $exportBlockName): string
	{
		$header = '';
		if (isset($this->moduleFieldInstances[$fieldName])) {
			$fieldModel = $this->moduleFieldInstances[$fieldName];
			if ($fieldModel) {
				$header = $fieldName;
				if ($exportBlockName) {
					$header = \App\Language::translate(\App\Purifier::decodeHtml($fieldModel->getBlockName()), $moduleName) . '::' . $header;
				}
			}
		}
		$referenceFieldName = $referenceField . $moduleName . $fieldName;
		if ($referenceField && isset($this->relatedModuleFields[$referenceFieldName])) {
			$fieldModel = $this->moduleFieldInstances[$referenceField];
			if ($fieldModel) {
				$header = $fieldName;
				if ($exportBlockName) {
					$header = \App\Language::translate(\App\Purifier::decodeHtml($fieldModel->getFieldLabel()), $moduleName) . '::' . $header;
				}
			}
		}
		return $header;
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
		$queryGenerator->setFields($this->getFieldsForExportQuery());
		if ($this->exportColumns) {
			foreach (explode(',', $this->exportColumns) as $index => $columnInfo) {
				[$relatedFieldName, $relatedModule, $referenceField] = array_pad(explode(':', $columnInfo), 3, null);
				if ($referenceField) {
					$queryGenerator->addRelatedField([
						'sourceField' => $referenceField,
						'relatedModule' => $relatedModule,
						'relatedField' => $relatedFieldName
					]);
				}
			}
		}
		if (!empty($this->queryOptions['entityState'])) {
			$queryGenerator->setStateCondition($this->queryOptions['entityState']);
		}
		$query = $queryGenerator->createQuery();
		$this->accessibleFields = $queryGenerator->getFields();
		switch ($this->queryOptions['mode']) {
			case 'ExportAllData':
				$query->limit(\App\Config::performance('MAX_NUMBER_EXPORT_RECORDS'));
				break;
			case 'ExportCurrentPage':
				$pagingModel = new \Vtiger_Paging_Model();
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
			default:
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
				$query->limit(\App\Config::performance('MAX_NUMBER_EXPORT_RECORDS'));
				break;
		}
		return $query;
	}

	/**
	 * Function return fields for export query by a specific method.
	 *
	 * @return array
	 */
	public function getFieldsForExportQuery(): array
	{
		if ($this->exportColumns && $this->quickExport) {
			$fields = $this->getFieldsSelectedByUserForQuery();
		} else {
			$fields = $this->getAllModuleFieldsForQuery();
		}
		return $fields;
	}

	/**
	 * Function returns all module fields for query.
	 *
	 * @return array
	 */
	public function getAllModuleFieldsForQuery(): array
	{
		$fields[] = 'id';
		foreach ($this->moduleFieldInstances as &$fieldModel) {
			// Check added as querygenerator is not checking this for admin users
			if ($fieldModel->isExportTable() && ($fieldModel->isViewEnabled() || $fieldModel->isMandatory())) {  // also export mandatory fields
				$fields[] = $fieldModel->getName();
			}
		}
		return $fields;
	}

	/**
	 * Function returns  module fields selected by user.
	 *
	 * @return array
	 */
	public function getFieldsSelectedByUserForQuery(): array
	{
		$fields[] = 'id';
		foreach (explode(',', $this->exportColumns) as $index => $columnInfo) {
			[$relatedFieldName, $relatedModule, $referenceField] = array_pad(explode(':', $columnInfo), 3, null);
			if ($referenceField) {
				$fields[] = $columnInfo;
			} else {
				$fields[] = $relatedFieldName;
			}
		}
		return $fields;
	}

	/**
	 * This function takes in an array of values for an user and sanitizes it for export
	 * Requires modification after adding a new field type.
	 *
	 * @param array $recordValues
	 */
	public function sanitizeValues(array $recordValues): array
	{
		if ($this->exportColumns) {
			$valuesToReturn = $this->getRecordDataInUserFormat($recordValues);
		} else {
			$valuesToReturn = $this->getRecordDataInExportFormat($recordValues);
		}
		return $valuesToReturn;
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

	public function getRecordDataInUserFormat(array $recordValues): array
	{
		foreach ($recordValues as $fieldName => &$value) {
			if (isset($this->moduleFieldInstances[$fieldName])) {
				$fieldModel = $this->moduleFieldInstances[$fieldName];
			} elseif (isset($this->relatedModuleFields[$fieldName])) {
				$fieldModel = $this->relatedModuleFields[$fieldName];
			} else {
				unset($recordValues[$fieldName]);
				continue;
			}
			$value = $fieldModel->getDisplayValue($value, false, false, true);
		}

		return $recordValues;
	}

	/**
	 * Function returns record data in export format.
	 *
	 * @param array $recordValues
	 *
	 * @return array
	 */
	public function getRecordDataInExportFormat(array $recordValues): array
	{
		$recordId = (int) ($recordValues['id'] ?? 0);
		foreach ($recordValues as $fieldName => &$value) {
			if (isset($this->moduleFieldInstances[$fieldName])) {
				$fieldModel = $this->moduleFieldInstances[$fieldName];
			} elseif (isset($this->relatedModuleFields[$fieldName])) {
				$fieldModel = $this->relatedModuleFields[$fieldName];
			} else {
				unset($recordValues[$fieldName]);
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
					if (isset($this->moduleFieldInstances[$fieldname])) {
						$this->picklistValues[$fieldname] = $this->moduleFieldInstances[$fieldname]->getPicklistValues();
					}
					if (isset($this->relatedModuleFields[$fieldname])) {
						$this->picklistValues[$fieldname] = $this->relatedModuleFields[$fieldname]->getPicklistValues();
					}
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
		}
		return $recordValues;
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
}
