<?php

/**
 * Export to XML model file.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Export to XML model class.
 */
class Vtiger_ExportToXml_Model extends \App\Export\Records
{
	protected $attrList = ['crmfield', 'crmfieldtype', 'partvalue', 'constvalue', 'refmoule', 'spec', 'refkeyfld', 'delimiter', 'testcondition'];
	protected $product = false;
	protected $tplName = '';
	protected $tmpXmlPath = '';
	protected $inventoryFields;
	protected $fileExtension = 'xml';

	/**
	 * Set template.
	 *
	 * @param string $tplName
	 *
	 * @return $this
	 */
	public function setTemplate(string $tplName)
	{
		$this->tplName = $tplName;

		return $this;
	}

	/** {@inheritdoc} */
	public function exportData()
	{
		$fileName = str_replace(' ', '_', \App\Purifier::decodeHtml(\App\Language::translate($this->moduleName, $this->moduleName)));
		$entriesInventory = [];
		$addInventoryData = $this->fullData && $this->moduleInstance->isInventory();
		$count = 0;
		$dataReader = $this->getExportQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$this->tmpXmlPath = 'cache/import/' . uniqid() . '_.xml';
			$this->xmlList[] = $this->tmpXmlPath;
			if ($addInventoryData) {
				$entriesInventory = $this->getEntriesInventory($row) ?: [];
			}
			if ($this->tplName) {
				$this->createXmlFromTemplate($row, $entriesInventory);
			} else {
				$this->createXml($this->sanitizeValues($row), $entriesInventory);
			}
			++$count;
		}
		if (1 < $count) {
			$this->outputZipFile($fileName);
		} else {
			$this->outputFile($fileName);
		}
	}

	/**
	 * Function returns data from advanced block.
	 *
	 * @param array $recordData
	 *
	 * @return array
	 */
	public function getEntriesInventory($recordData): array
	{
		$entries = [];
		$inventoryModel = Vtiger_Inventory_Model::getInstance($this->moduleName);
		$this->inventoryFields = $inventoryModel->getFields();
		$table = $inventoryModel->getDataTableName();
		$dataReader = (new \App\Db\Query())->from($table)->where(['crmid' => $recordData['id']])->orderBy(['seq' => SORT_ASC])->createCommand()->query();
		while ($inventoryRow = $dataReader->read()) {
			$entries[] = $inventoryRow;
		}
		$dataReader->close();

		return $entries;
	}

	/**
	 * Sanitize inventory value.
	 *
	 * @param mixed  $value
	 * @param string $columnName
	 *
	 * @return string
	 */
	public function sanitizeInventoryValue($value, $columnName): string
	{
		if ($field = $this->inventoryFields[$columnName] ?? false) {
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
		} elseif (\in_array($columnName, ['taxparam', 'discountparam', 'currencyparam'])) {
			if ('currencyparam' === $columnName) {
				$field = $this->inventoryFields['currency'];
				$valueData = $field->getCurrencyParam([], $value);
				$valueNewData = [];
				foreach ($valueData as $currencyId => &$data) {
					$currencyName = \App\Fields\Currency::getById($currencyId)['currency_name'];
					$data['value'] = $currencyName;
					$valueNewData[$currencyName] = $data;
				}
				$value = \App\Json::encode($valueNewData);
			}
		}
		return html_entity_decode($value);
	}

	public function outputFile($fileName)
	{
		header("content-disposition: attachment;filename=$fileName.xml");
		header('content-type: text/csv;charset=UTF-8');
		header('expires: Mon, 31 Dec 2000 00:00:00 GMT');
		header('last-modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('cache-control: post-check=0, pre-check=0', false);

		readfile($this->tmpXmlPath);
		unlink($this->tmpXmlPath);
	}

	protected function outputZipFile($fileName)
	{
		$zipName = 'cache/import/' . uniqid() . '.zip';

		$zip = new ZipArchive();
		$zip->open($zipName, ZipArchive::CREATE);
		$countXmlList = \count($this->xmlList);
		for ($i = 0; $i < $countXmlList; ++$i) {
			$xmlFile = basename($this->xmlList[$i]);
			$xmlFile = explode('_', $xmlFile);
			array_shift($xmlFile);
			$xmlFile = $fileName . $i . implode('_', $xmlFile);
			$zip->addFile($this->xmlList[$i], $xmlFile);
		}
		$zip->close();

		header("content-disposition: attachment;filename=$fileName.zip");
		header('content-type: application/zip');
		header('expires: Mon, 31 Dec 2000 00:00:00 GMT');
		header('last-modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('cache-control: post-check=0, pre-check=0', false);
		readfile($zipName);
		unlink($zipName);
		array_map('unlink', $this->xmlList);
	}

	/**
	 * Create XML file.
	 *
	 * @param array $entries
	 * @param array $entriesInventory
	 *
	 * @return void
	 */
	public function createXml($entries, $entriesInventory)
	{
		$exportBlockName = \App\Config::component('Export', 'BLOCK_NAME');
		$xml = new \XMLWriter();
		$xml->openMemory();
		$xml->setIndent(true);
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElement('MODULE_FIELDS');
		foreach ($this->fields as $fieldName => $fieldModel) {
			if ($fieldModel->get('source_field_name')) {
				continue;
			}
			$xml->startElement($fieldName);
			$header = \App\Language::translate(\App\Purifier::decodeHtml($fieldModel->get('label')), $this->moduleName);
			if ($exportBlockName) {
				$header = \App\Language::translate(\App\Purifier::decodeHtml($fieldModel->getBlockName()), $this->moduleName) . '::' . $header;
			}
			$xml->writeAttribute('type', $fieldModel->getFieldDataType());
			$xml->writeAttribute('label', $header);
			if ($this->isCData($fieldName)) {
				$xml->writeCData($entries[$fieldName]);
			} else {
				$xml->text($entries[$fieldName]);
			}
			$xml->endElement();
		}
		if ($entriesInventory) {
			$customColumns = [];
			$xml->startElement('INVENTORY_ITEMS');
			foreach ($entriesInventory as $inventory) {
				unset($inventory['id'], $inventory['crmid']);

				$xml->startElement('INVENTORY_ITEM');
				foreach ($inventory as $columnName => $value) {
					$xml->startElement($columnName);
					if ($fieldModel = ($this->inventoryFields[$columnName] ?? false)) {
						$xml->writeAttribute('label', \App\Language::translate(html_entity_decode($fieldModel->get('label'), ENT_QUOTES), $this->moduleName));
						if (!\in_array($columnName, $customColumns)) {
							foreach ($fieldModel->getCustomColumn() as $key => $dataType) {
								$customColumns[$key] = $columnName;
							}
						}
					}
					if ($this->isCData($columnName, $customColumns)) {
						$xml->writeCData($this->sanitizeInventoryValue($value, $columnName));
					} else {
						$xml->text($this->sanitizeInventoryValue($value, $columnName));
					}
					$xml->endElement();
				}
				$xml->endElement();
			}
			$xml->endElement();
		}
		$xml->endElement();
		file_put_contents($this->tmpXmlPath, $xml->flush(true), FILE_APPEND);
	}

	public function isCData($name, $customColumns = [])
	{
		if ($customColumns) {
			return \array_key_exists($name, $customColumns);
		}
		if (($fieldModel = $this->moduleFieldInstances[$name] ?? false) && \in_array($fieldModel->getFieldDataType(), ['text', 'multiEmail'])) {
			return true;
		}
		return false;
	}

	public function createXmlFromTemplate($entries, $entriesInventory)
	{
	}
}
