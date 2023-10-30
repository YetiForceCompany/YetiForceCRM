<?php
/**
 * Abstract base view controller file.
 *
 * @package   Export
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Export;

/**
 * Abstract export records class.
 */
abstract class Records extends \App\Base
{
	/** @var int Data export in a format that can be imported later */
	public const EXPORT_FORMAT = 0;

	/** @var int Data export in user format */
	public const USER_FORMAT = 1;

	/** @var string Module name */
	protected $moduleName;

	/** @var \Vtiger_Module_Model Module model. */
	protected $moduleInstance;

	/** @var \Vtiger_Field_Model[] Field model instance. */
	protected $moduleFieldInstances;

	/** @var string File extension */
	protected $fileExtension = '';

	/** @var array Headers field */
	protected $headers = [];

	/** @var array Fields for export */
	protected $fields = [];

	/** @var int Limit of exported entries */
	protected $limit;

	/** @var bool Export all data */
	protected $format = self::EXPORT_FORMAT;

	/** @var bool Export all data */
	public $fullData = false;

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
		$instance = new $modelClassName();
		$instance->fileExtension = $exportType;
		$instance->moduleName = $moduleName;
		$instance->moduleInstance = \Vtiger_Module_Model::getInstance($moduleName);
		$instance->moduleFieldInstances = $instance->moduleInstance->getFields();
		$instance->queryGenerator = new \App\QueryGenerator($moduleName);

		return $instance;
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
	 * Function exports the data.
	 */
	abstract public function exportData();

	/**
	 * Set the format of the exported data.
	 *
	 * @param int $format self::EXPORT_FORMAT or self::USER_FORMAT
	 *
	 * @return $this
	 */
	public function setFormat(int $format)
	{
		$this->format = $format;

		return $this;
	}

	/**
	 * Get query generator object.
	 *
	 * @return \App\QueryGenerator
	 */
	public function getQueryGenerator(): \App\QueryGenerator
	{
		return $this->queryGenerator;
	}

	/**
	 * Set.
	 *
	 * @param array $fields
	 *
	 * @return $this
	 */
	public function setFields(array $fields)
	{
		foreach ($fields as $fieldName) {
			$this->setField($fieldName);
		}

		return $this;
	}

	/**
	 * Load fields from custom view.
	 *
	 * @param int $cvId
	 *
	 * @return void
	 */
	public function loadFieldsFromCvId(int $cvId): void
	{
		foreach (\App\CustomView::getInstance($this->moduleName)->getColumnsListByCvid($cvId) as $fieldInfo) {
			['field_name' => $relatedFieldName, 'module_name' => $relatedModule, 'source_field_name' => $referenceField] = $fieldInfo;
			$cvFieldData = $referenceField ? "{$relatedFieldName}:{$relatedModule}:{$referenceField}" : $relatedFieldName;
			if (($fieldModel = $this->setField($cvFieldData)) && $fieldInfo['label']) {
				$fieldModel->set('isLabelCustomized', true);
				$fieldModel->set('label', $fieldInfo['label']);
			}
		}
	}

	/**
	 * Set field model.
	 *
	 * @param string $fieldName
	 *
	 * @return Vtiger_Field_Model|false
	 */
	public function setField(string $fieldName)
	{
		$fieldModel = null;
		[$relatedFieldName, $relatedModule, $referenceField] = array_pad(explode(':', $fieldName), 3, null);

		if ($referenceField) {
			$relatedFieldModel = \Vtiger_Module_Model::getInstance($relatedModule)->getFieldByName($relatedFieldName);
			if (!isset($this->moduleFieldInstances[$referenceField]) || !$relatedFieldModel) {
				throw new \App\Exceptions\IllegalValue("ERR_FIELD_NOT_FOUND||{$relatedFieldName}");
			}
			$fieldModel = clone $relatedFieldModel;
			$fieldModel->set('source_field_name', $referenceField);
		} elseif (!empty($this->moduleFieldInstances[$relatedFieldName])) {
			$fieldModel = clone $this->moduleFieldInstances[$relatedFieldName];
		}

		if ($fieldModel && $fieldModel->isExportable()) {
			$result = $this->fields[$fieldModel->getFullName()] = $fieldModel;
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * Set a limit for exported data.
	 *
	 * @param int $limit
	 *
	 * @return $this
	 */
	public function setLimit(int $limit)
	{
		$this->limit = $limit;

		return $this;
	}

	/**
	 * Get file headers.
	 *
	 * @return array
	 */
	public function getHeaders(): array
	{
		if (!$this->headers) {
			if ($this->fullData) {
				$this->headers = $this->getAllModuleFieldsAsHeaders();
			} else {
				foreach ($this->fields as $fieldModel) {
					$label = $fieldModel->getFullLabelTranslation($this->moduleInstance);
					$this->headers[] = \App\Purifier::decodeHtml($label);
				}
			}
		}

		return $this->headers;
	}

	/**
	 * Get fields for query.
	 *
	 * @return array
	 */
	public function getFieldsForQuery(): array
	{
		if ($this->fullData) {
			$this->setFields(array_keys($this->moduleFieldInstances));
		}

		return $this->fields;
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
		foreach ($this->moduleFieldInstances as $fieldModel) {
			if ($fieldModel->isExportable()) {
				$header = \App\Language::translate(\App\Purifier::decodeHtml($fieldModel->getFieldLabel()), $this->moduleName);
				if ($exportBlockName) {
					$header = \App\Language::translate(\App\Purifier::decodeHtml($fieldModel->getBlockName()), $this->moduleName) . '::' . $header;
				}
				$headers[] = $header;
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
	 * Function that generates Export Query object.
	 *
	 * @return \App\Db\Query
	 */
	public function getExportQuery(): \App\Db\Query
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->clearFields()->setLimit($this->limit);
		$queryFields = $this->getFieldsForQuery();
		if (!$queryFields) {
			$queryGenerator->setFields(['id'])->addCondition('id', 0, 'e');
		}
		foreach (array_keys($queryFields) as $fieldName) {
			$queryGenerator->setField($fieldName);
		}
		return $queryGenerator->createQuery();
	}

	/**
	 * This function takes in an array of values for an user and sanitizes it for export
	 * Requires modification after adding a new field type.
	 *
	 * @param array $recordValues
	 */
	public function sanitizeValues(array $recordValues): array
	{
		if (self::USER_FORMAT === $this->format) {
			$valuesToReturn = $this->getDataInUserFormat($recordValues);
		} else {
			$valuesToReturn = $this->getDataInExportFormat($recordValues);
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

	/**
	 * Get data from record in display format.
	 *
	 * @param array $recordValues
	 *
	 * @return array
	 */
	public function getDataInUserFormat(array $recordValues): array
	{
		$response = [];
		foreach ($this->fields as $dbKey => $fieldModel) {
			$idKey = 'id';
			if ($fieldModel->get('source_field_name')) {
				$name = $fieldModel->get('source_field_name') . $fieldModel->getModuleName();
				$idKey = $name . $idKey;
				$dbKey = $name . $fieldModel->getName();
			}

			$response[$fieldModel->getFullName()] = $this->getDisplayValue($fieldModel, $recordValues[$dbKey], $recordValues[$idKey] ?? 0, $recordValues);
		}

		return $response;
	}

	/**
	 * Function returns record data in export format.
	 *
	 * @param array $recordValues
	 *
	 * @return array
	 */
	public function getDataInExportFormat(array $recordValues): array
	{
		$response = [];
		foreach ($this->fields as $dbKey => $fieldModel) {
			$idKey = 'id';
			if ($fieldModel->get('source_field_name')) {
				$name = $fieldModel->get('source_field_name') . $fieldModel->getModuleName();
				$idKey = $name . $idKey;
				$dbKey = $name . $fieldModel->getName();
			}

			$response[$fieldModel->getFullName()] = $fieldModel->getUITypeModel()->getValueToExport($recordValues[$dbKey], $recordValues[$idKey] ?? 0);
		}

		return $response;
	}

	/**
	 * Get display value.
	 *
	 * @param \Vtiger_Field_Model $fieldModel
	 * @param mixed               $value
	 * @param int                 $recordId
	 * @param array               $rowData
	 * @param int                 $length
	 *
	 * @return string
	 */
	public function getDisplayValue(\Vtiger_Field_Model $fieldModel, $value, int $recordId, array $rowData, $length = 65000)
	{
		if ('sharedOwner' === $fieldModel->getFieldDataType() && $recordId) {
			$value = implode(',', \App\Fields\SharedOwner::getById($recordId));
		}
		$returnValue = $fieldModel->getDisplayValue($value, $recordId, null, true, $length);
		if (\is_string($returnValue)) {
			$returnValue = \App\Purifier::decodeHtml($returnValue);
		}

		return $returnValue && 'text' === $fieldModel->getFieldDataType() ? strip_tags($returnValue) : $returnValue;
	}

	/**
	 * Send HTTP Header.
	 *
	 * @return void
	 */
	public function sendHttpHeader(): void
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
		return \App\Utils::sanitizeSpecialChars(\App\Purifier::decodeHtml(\App\Language::translate($this->moduleName, $this->moduleName))) . ".{$this->fileExtension}";
	}
}
