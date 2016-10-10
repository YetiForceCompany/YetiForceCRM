<?php

/**
 * ExportToXml Model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_ExportToXml_Model extends Vtiger_Export_Model
{

	protected $attrList = ['crmfield', 'crmfieldtype', 'partvalue', 'constvalue', 'refmoule', 'spec', 'refkeyfld', 'delimiter', 'testcondition'];
	protected $product = false;
	protected $tplName = '';
	protected $tmpXmlPath = '';
	protected $index;
	protected $inventoryFields;

	public function exportData(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		if ($request->get('xmlExportType')) {
			$this->tplName = $request->get('xmlExportType');
		}
		$query = $this->getExportQuery($request);
		$result = $db->query($query);

		$fileName = str_replace(' ', '_', decode_html(vtranslate($this->moduleName, $this->moduleName)));

		$entries = $db->getArray($result);
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
				$this->createXml($this->sanitizeValues($data), $entriesInventory[$key]);
			}
		}
		if (1 < count($entries)) {
			$this->outputZipFile($fileName);
		} else {
			$this->outputFile($fileName);
		}
	}

	public function getEntriesInventory($recordData)
	{
		$db = PearDatabase::getInstance();
		$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($this->moduleName);
		$this->inventoryFields = $inventoryFieldModel->getFields();
		$table = $inventoryFieldModel->getTableName('data');
		$query = 'SELECT * FROM %s WHERE id = ? ORDER BY seq';
		$query = sprintf($query, $table);
		$resultInventory = $db->pquery($query, [$recordData[$this->focus->table_index]]);
		if ($db->getRowCount($resultInventory)) {
			while ($inventoryRow = $db->getRow($resultInventory)) {
				$entries[] = $inventoryRow;
			}
		}
		return $entries;
	}

	public function sanitizeInventoryValue($value, $columnName, $formated = false)
	{
		$inventoryFieldModel = Vtiger_InventoryField_Model::getInstance($this->moduleName);
		$inventoryFields = $inventoryFieldModel->getFields();
		$field = $inventoryFields[$columnName];
		if (!empty($field)) {
			if (in_array($field->getName(), ['Name', 'Reference'])) {
				$value = trim($value);
				if (!empty($value)) {
					$recordModule = \vtlib\Functions::getCRMRecordType($value);
					$displayValueArray = \includes\Record::computeLabels($recordModule, $value);
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
			} elseif ($formated && !in_array($field->getName(), ['DiscountMode', 'TaxMode'])) {
				$value = $field->getDisplayValue($value);
			} else {
				$value;
			}
		} elseif (in_array($columnName, ['taxparam', 'discountparam', 'currencyparam'])) {
			switch ($columnName) {
//				case 'taxparam':
//					$tax = Vtiger_InventoryField_Model::getTaxParam($value, 0, false);
//					$value = key($tax);
//					break;
				case 'currencyparam':
					$field = $inventoryFields['currency'];
					$valueData = $field->getCurrencyParam([], $value);
					$valueNewData = [];
					foreach ($valueData as $currencyId => &$data) {
						$currencyName = vtlib\Functions::getCurrencyName($currencyId, false);
						$data['value'] = $currencyName;
						$valueNewData[$currencyName] = $data;
					}
					$value = \includes\utils\Json::encode($valueNewData);
					break;
				default:
					break;
			}
		}
		return html_entity_decode($value);
	}

	public function outputFile($fileName)
	{
		header("Content-Disposition:attachment;filename=$fileName.xml");
		header("Content-Type:text/csv;charset=UTF-8");
		header("Expires: Mon, 31 Dec 2000 00:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: post-check=0, pre-check=0", false);

		readfile($this->tmpXmlPath);
	}

	protected function outputZipFile($fileName)
	{

		$zipName = 'cache/import/' . uniqid() . '.zip';

		$zip = new ZipArchive();
		$zip->open($zipName, ZipArchive::CREATE);

		for ($i = 0; $i < count($this->xmlList); $i++) {
			$xmlFile = basename($this->xmlList[$i]);
			$xmlFile = explode('_', $xmlFile);
			array_shift($xmlFile);
			$xmlFile = $fileName . $i . implode('_', $xmlFile);
			$zip->addFile($this->xmlList[$i], $xmlFile);
		}

		$zip->close();

		header("Content-Disposition:attachment;filename=$fileName.zip");
		header("Content-Type:application/zip");
		header("Expires: Mon, 31 Dec 2000 00:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: post-check=0, pre-check=0", false);

		readfile($zipName);
	}

	public function createXml($entries, $entriesInventory)
	{
		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->setIndent(TRUE);
		$xml->startDocument('1.0', 'UTF-8');

		$xml->startElement('MODULE_FIELDS');
		foreach ($this->moduleFieldInstances as $fieldName => $fieldModel) {
			if (!in_array($fieldModel->get('presence'), [0, 2])) {
				continue;
			}
			$xml->startElement($fieldName);
			$xml->writeAttribute('label', vtranslate(html_entity_decode($fieldModel->get('label'), ENT_QUOTES), $this->moduleName));
			if ($this->isCData($fieldName)) {
				$xml->writeCData($entries[$fieldName]);
			} else {
				$xml->text($entries[$fieldModel->get('column')]);
			}
			$xml->endElement();
		}
		if ($entriesInventory) {
			$customColumns = [];
			$xml->startElement('INVENTORY_ITEMS');
			foreach ($entriesInventory as $inventory) {
				unset($inventory['id']);
				$xml->startElement('INVENTORY_ITEM');
				while (list($columnName, $value) = each($inventory)) {
					$xml->startElement($columnName);
					$fieldModel = $this->inventoryFields[$columnName];
					if ($fieldModel) {
						$xml->writeAttribute('label', vtranslate(html_entity_decode($fieldModel->get('label'), ENT_QUOTES), $this->moduleName));
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
		$fieldModel = $this->moduleFieldInstances[$name];
		if ($fieldModel && $fieldModel->getFieldDataType() == 'text') {
			return true;
		}
		return false;
	}

	public function createXmlFromTemplate($entries, $entriesInventory)
	{
		
	}
}
