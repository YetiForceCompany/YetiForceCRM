<?php
/* * ***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PieChart extends Base_Chart
{

	public function generateData()
	{
		$db = PearDatabase::getInstance();
		$values = [];
		$chartSQL = $this->getQuery();
		$result = $db->pquery($chartSQL, []);
		$rows = $db->numRows($result);

		$queryColumnsByFieldModel = $this->getQueryColumnsByFieldModel();
		if (is_array($queryColumnsByFieldModel)) {
			foreach ($queryColumnsByFieldModel as $field) {
				$sector = $field->get('reportlabel');
				$sectorField = $field;
			}
		}

		if ($this->isRecordCount()) {
			$sector = 'RECORD_COUNT';
		}

		$groupByColumnsByFieldModel = $this->getGroupbyColumnsByFieldModel();

		if (is_array($groupByColumnsByFieldModel)) {
			foreach ($groupByColumnsByFieldModel as $groupField) {
				$legend = $groupField->get('reportlabel');
				$legendField = $groupField;
			}
		}

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$currencyRateAndSymbol = \vtlib\Functions::getCurrencySymbolandRate($currentUserModel->currency_id);

		for ($i = 0; $i < $rows; $i++) {
			$row = $db->queryResultRowData($result, $i);
			$value = (float) $row[$sector];
			if (!$this->isRecordCount()) {
				if ($sectorField) {
					if ($sectorField->get('uitype') != '7') {
						if ($sectorField->get('uitype') == '71' || $sectorField->get('uitype') == '72') { //convert currency fields
							$value = CurrencyField::convertFromDollar($value, $currencyRateAndSymbol['rate']);
						} else {
							$value = (int) $sectorField->getDisplayValue($row[$sector]);
						}
					}
				}
			}
			$values[] = $value;
			//translate picklist and multiselect picklist values
			if ($legendField) {
				$fieldDataType = $legendField->getFieldDataType();
				if ($fieldDataType === 'picklist') {
					$label = \App\Language::translate($row[$legend], $legendField->getModuleName());
				} else if ($fieldDataType === 'multipicklist') {
					$multiPicklistValue = $row[$legend];
					$multiPicklistValues = explode(' |##| ', $multiPicklistValue);
					foreach ($multiPicklistValues as $multiPicklistValue) {
						$labelList[] = \App\Language::translate($multiPicklistValue, $legendField->getModuleName());
					}
					$label = implode(',', $labelList);
				} else if ($fieldDataType === 'date') {
					$label = App\Fields\Date::formatToDisplay($row[strtolower($legendField->get('reportlabel'))]);
				} else if ($fieldDataType === 'datetime') {
					$label = App\Fields\DateTime::formatToDisplay($row[strtolower($legendField->get('reportlabel'))]);
				} else {
					$label = $row[$legend];
				}
			} else {
				$label = $row[$legend];
			}
			$labels[] = (strlen($label) > 30) ? substr($label, 0, 30) . '..' : $label;
			$links[] = $this->generateLink($legendField->get('reportcolumninfo'), $row[strtolower($legend)]);
		}

		$data = ['labels' => $labels,
			'values' => $values,
			'links' => $links,
			'graph_label' => $this->getGraphLabel()
		];
		return $data;
	}
}
