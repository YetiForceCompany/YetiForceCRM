<?php

/**
 * ExportToXml Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_ExportToXml_Model extends Vtiger_Export_Model
{
	protected $attrList = ['crmfield', 'crmfieldtype', 'partvalue', 'constvalue', 'refmoule', 'spec', 'refkeyfld', 'delimiter', 'testcondition'];
	protected $product = false;
	protected $tplName = '';
	protected $tmpXmlPath = '';
	protected $index;
	protected $inventoryFields;

	/**
	 * {@inheritdoc}
	 */
	public function initializeFromRequest(\App\Request $request)
	{
		parent::initializeFromRequest($request);
		if ($request->has('xmlExportType')) {
			$this->tplName = $request->getByType('xmlExportType', 'Text');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function exportData()
	{
		$query = $this->getExportQuery();
		$fileName = str_replace(' ', '_', \App\Purifier::decodeHtml(\App\Language::translate($this->moduleName, $this->moduleName)));
		$entries = $query->all();
		$entriesInventory = [];
		if ($this->moduleInstance->isInventory()) {
			foreach ($entries as $key => $recordData) {
				$entriesInventory[$key] = $this->getEntriesInventory($recordData);
			}
		}
		foreach ($entries as $key => $data) {
			$this->tmpXmlPath = 'cache/import/' . uniqid() . '_.xml';
			$this->xmlList[] = $this->tmpXmlPath;
			$this->index = $key;
			if ($this->tplName) {
				$this->createXmlFromTemplate($data, $data);
			} else {
				$this->createXml($this->sanitizeValues($data), $entriesInventory[$key] ?? []);
			}
		}
		if (1 < count($entries)) {
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
	public function getEntriesInventory($recordData)
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

	public function sanitizeInventoryValue($value, $columnName, $formated = false)
	{
		if ($field = $this->inventoryFields[$columnName] ?? false) {
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
		} elseif (in_array($columnName, ['taxparam', 'discountparam', 'currencyparam'])) {
			if ($columnName === 'currencyparam') {
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
		$countXmlList = count($this->xmlList);
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

	public function createXml($entries, $entriesInventory)
	{
		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->setIndent(true);
		$xml->startDocument('1.0', 'UTF-8');

		$xml->startElement('MODULE_FIELDS');
		foreach ($this->moduleFieldInstances as $fieldName => $fieldModel) {
			if (!in_array($fieldModel->get('presence'), [0, 2])) {
				continue;
			}
			$xml->startElement($fieldName);
			$xml->writeAttribute('label', \App\Language::translate(html_entity_decode($fieldModel->get('label'), ENT_QUOTES), $this->moduleName));
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
						if (!in_array($columnName, $customColumns)) {
							foreach ($fieldModel->getCustomColumn() as $key => $dataType) {
								$customColumns[$key] = $columnName;
							}
						}
					}
					if ($this->isCData($columnName, $customColumns)) {
						$xml->writeCData($this->sanitizeInventoryValue($value, $columnName, true));
					} else {
						$xml->text($this->sanitizeInventoryValue($value, $columnName, true));
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
			return array_key_exists($name, $customColumns);
		}
		if (($fieldModel = $this->moduleFieldInstances[$name] ?? false) && $fieldModel->getFieldDataType() === 'text') {
			return true;
		}
		return false;
	}

	public function createXmlFromTemplate($entries, $entriesInventory)
	{
	}
}
