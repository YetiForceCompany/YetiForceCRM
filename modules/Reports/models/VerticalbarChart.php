<?php
/* * ***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class VerticalbarChart extends Base_Chart
{

	public function generateData()
	{
		$db = PearDatabase::getInstance();
		$chartSQL = $this->getQuery();

		$result = $db->pquery($chartSQL, []);
		$rows = $db->numRows($result);
		$values = [];

		$queryColumnsByFieldModel = $this->getQueryColumnsByFieldModel();

		$recordCountLabel = '';
		if ($this->isRecordCount()) {
			$recordCountLabel = 'RECORD_COUNT';
		}

		$groupByColumnsByFieldModel = $this->getGroupbyColumnsByFieldModel();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$currencyRateAndSymbol = \vtlib\Functions::getCurrencySymbolandRate($currentUserModel->currency_id);
		$links = [];

		for ($i = 0; $i < $rows; $i++) {
			$row = $db->queryResultRowData($result, $i);

			if ($recordCountLabel) {
				$values[$i][] = (int) $row[$recordCountLabel];
			}

			if ($queryColumnsByFieldModel) {
				foreach ($queryColumnsByFieldModel as $fieldModel) {
					if ($fieldModel->get('uitype') == '71' || $fieldModel->get('uitype') == '72') {
						$value = (float) ($row[$fieldModel->get('reportlabel')]);
						$values[$i][] = CurrencyField::convertFromDollar($value, $currencyRateAndSymbol['rate']);
					} else {
						$values[$i][] = (float) $row[$fieldModel->get('reportlabel')];
					}
				}
			}

			if ($groupByColumnsByFieldModel) {
				foreach ($groupByColumnsByFieldModel as $gFieldModel) {
					$fieldDataType = $gFieldModel->getFieldDataType();
					if ($fieldDataType === 'picklist') {
						$label = \App\Language::translate($row[$gFieldModel->get('reportlabel')], $gFieldModel->getModuleName());
					} else if ($fieldDataType === 'multipicklist') {
						$multiPicklistValue = $row[$gFieldModel->get('reportlabel')];
						$multiPicklistValues = explode(' |##| ', $multiPicklistValue);
						foreach ($multiPicklistValues as $multiPicklistValue) {
							$labelList[] = \App\Language::translate($multiPicklistValue, $gFieldModel->getModuleName());
						}
						$label = implode(',', $labelList);
					} else if ($fieldDataType === 'date') {
						$columnInfo = explode(':', $gFieldModel->get('reportcolumninfo'));
						$label = $row[$gFieldModel->get('reportlabel')];
						if (isset($columnInfo[5]) && $columnInfo[5] === 'MY') {
							$m = explode(' ', $label);
							$label = App\Language::translate('LBL_' . date('M', strtotime($m[1] . '-' . $m[0] . '-' . '1'))) . ' ' . $m[1];
						} else {
							$label = App\Fields\Date::formatToDisplay($label);
						}
					} else if ($fieldDataType === 'datetime') {
						$label = $row[$gFieldModel->get('reportlabel')];
						$columnInfo = explode(':', $gFieldModel->get('reportcolumninfo'));
						if (isset($columnInfo[5]) && $columnInfo[5] === 'MY') {
							$m = explode(' ', $label);
							$label = App\Language::translate('LBL_' . date('M', strtotime($m[1] . '-' . $m[0] . '-' . '1'))) . ' ' . $m[1];
						}
					} else {
						$label = $row[$gFieldModel->get('reportlabel')];
					}
					$labels[] = (strlen($label) > 30) ? substr($label, 0, 30) . '..' : $label;
					$links[] = $this->generateLink($gFieldModel->get('reportcolumninfo'), $row[$gFieldModel->get('reportlabel')]);
				}
			}
		}

		$data = ['labels' => $labels,
			'values' => $values,
			'links' => $links,
			'type' => (count($values[0]) == 1) ? 'singleBar' : 'multiBar',
			'data_labels' => $this->getDataLabels(),
			'graph_label' => $this->getGraphLabel()
		];
		return $data;
	}

	public function getDataLabels()
	{
		$dataLabels = [];
		if ($this->isRecordCount()) {
			$dataLabels[] = \App\Language::translate('LBL_RECORD_COUNT', 'Reports');
		}
		$queryColumnsByFieldModel = $this->getQueryColumnsByFieldModel();
		if ($queryColumnsByFieldModel) {
			foreach ($queryColumnsByFieldModel as $fieldModel) {
				$fieldTranslatedLabel = $this->getTranslatedLabelFromReportLabel($fieldModel->get('reportlabel'));
				$reportColumn = $fieldModel->get('reportcolumninfo');
				$reportColumnInfo = explode(':', $reportColumn);

				$aggregateFunction = $reportColumnInfo[5];
				$aggregateFunctionLabel = $this->getAggregateFunctionLabel($aggregateFunction);
				$dataLabels[] = \App\Language::translateArgs($aggregateFunctionLabel, 'Reports', $fieldTranslatedLabel);
			}
		}
		return $dataLabels;
	}
}
