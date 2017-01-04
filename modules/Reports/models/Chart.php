<?php
/* * ***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Reports_Chart_Model extends Vtiger_Base_Model
{

	public static function getInstanceById($reportModel)
	{
		$self = new self();
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_reporttype WHERE reportid = ?', array($reportModel->getId()));
		$data = $db->query_result($result, 0, 'data');
		if (!empty($data)) {
			$decodeData = \App\Json::decode(decode_html($data));
			$self->setData($decodeData);
			$self->setParent($reportModel);
			$self->setId($reportModel->getId());
		}
		return $self;
	}

	public function getId()
	{
		return $this->get('reportid');
	}

	public function setId($id)
	{
		$this->set('reportid', $id);
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setParent($parent)
	{
		$this->parent = $parent;
	}

	public function getChartType()
	{
		$type = $this->get('type');
		if (empty($type))
			$type = 'pieChart';
		return $type;
	}

	public function getGroupByField()
	{
		return $this->get('groupbyfield');
	}

	public function getDataFields()
	{
		return $this->get('datafields');
	}

	public function getData()
	{
		$type = ucfirst($this->getChartType());
		$chartModel = new $type($this);
		return $chartModel->generateData();
	}
}

abstract class Base_Chart extends Vtiger_Base_Model
{

	public function __construct($parent)
	{
		$this->setParent($parent);
		$this->setReportRunObject();

		$this->setQueryColumns($this->getParent()->getDataFields());
		$this->setGroupByColumns($this->getParent()->getGroupByField());
	}

	public function setParent($parent)
	{
		$this->parent = $parent;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function getReportModel()
	{
		$parent = $this->getParent();
		return $parent->getParent();
	}

	public function isRecordCount()
	{
		return $this->isRecordCount;
	}

	public function setRecordCount()
	{
		$this->isRecordCount = true;
	}

	public function setReportRunObject()
	{
		$chartModel = $this->getParent();
		$reportModel = $chartModel->getParent();
		$this->reportRun = ReportRun::getInstance($reportModel->getId());
	}

	public function getReportRunObject()
	{
		return $this->reportRun;
	}

	public function getFieldModelByReportColumnName($column)
	{
		$fieldInfo = explode(':', $column);
		$moduleFieldLabelInfo = explode('__', $fieldInfo[2]);
		$moduleName = $moduleFieldLabelInfo[0];
		$fieldName = $fieldInfo[3];

		if ($moduleName && $fieldName) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			return $moduleModel->getField($fieldName);
		}
		return false;
	}

	public function getQueryColumnsByFieldModel()
	{
		return $this->fieldModels;
	}

	public function setQueryColumns($columns)
	{
		if ($columns && is_string($columns))
			$columns = array($columns);

		if (is_array($columns)) {
			foreach ($columns as $column) {
				if ($column == 'count(*)') {
					$this->setRecordCount();
				} else {

					$fieldModel = $this->getFieldModelByReportColumnName($column);
					$columnInfo = explode(':', $column);

					$referenceFieldReportColumnSQL = $this->getReportRunObject()->getEscapedColumns($columnInfo);

					$aggregateFunction = $columnInfo[5];
					if (empty($referenceFieldReportColumnSQL)) {
						$reportColumnSQL = $this->getReportTotalColumnSQL($columnInfo);
						$reportColumnSQLInfo = preg_split('/ AS /i', $reportColumnSQL);

						if ($aggregateFunction == 'AVG') { // added as mysql will ignore null values
							$label = $this->reportRun->replaceSpecialChar($reportColumnSQLInfo[1]) . '__AVG';
							$reportColumn = '(SUM(' . $reportColumnSQLInfo[0] . ')/COUNT(*)) AS ' . $label;
						} else {
							$label = $this->reportRun->replaceSpecialChar($reportColumnSQLInfo[1]) . '__' . $aggregateFunction;
							$reportColumn = $aggregateFunction . '(' . $reportColumnSQLInfo[0] . ') AS ' . $label;
						}

						$fieldModel->set('reportcolumn', $reportColumn);
						$fieldModel->set('reportlabel', $this->reportRun->replaceSpecialChar($label));
					} else {
						$reportColumn = $referenceFieldReportColumnSQL;
						$groupColumnSQLInfo = preg_split('/ AS /i', $referenceFieldReportColumnSQL);
						$fieldModel->set('reportlabel', $this->reportRun->replaceSpecialChar($groupColumnSQLInfo[1]));
						$fieldModel->set('reportcolumn', $this->reportRun->replaceSpecialChar($reportColumn));
					}

					$fieldModel->set('reportcolumninfo', $column);

					if ($fieldModel) {
						$fieldModels[] = $fieldModel;
					}
				}
			}
		}
		if ($fieldModels)
			$this->fieldModels = $fieldModels;
	}

	public function setGroupByColumns($columns)
	{
		if ($columns && is_string($columns))
			$columns = array($columns);

		if (is_array($columns)) {
			foreach ($columns as $column) {
				$fieldModel = $this->getFieldModelByReportColumnName($column);

				if ($fieldModel) {
					$columnInfo = explode(':', $column);

					$referenceFieldReportColumnSQL = $this->getReportRunObject()->getEscapedColumns($columnInfo);
					if (empty($referenceFieldReportColumnSQL)) {
						$reportColumnSQL = $this->getReportColumnSQL($columnInfo);
						$fieldModel->set('reportcolumn', $this->reportRun->replaceSpecialChar($reportColumnSQL));
						// Added support for date and date time fields with Year and Month support
						if ($columnInfo[4] == 'D' || $columnInfo[4] == 'DT') {
							$reportColumnSQLInfo = preg_split('/ AS /i', $reportColumnSQL);
							$fieldModel->set('reportlabel', trim($this->reportRun->replaceSpecialChar($reportColumnSQLInfo[1]), '\'')); // trim added as single quote on labels was not grouping properly
						} else {
							$fieldModel->set('reportlabel', $this->reportRun->replaceSpecialChar($columnInfo[2]));
						}
					} else {
						$groupColumnSQLInfo = preg_split('/ AS /i', $referenceFieldReportColumnSQL);
						$fieldModel->set('reportlabel', $this->reportRun->replaceSpecialChar($groupColumnSQLInfo[1]));
						$fieldModel->set('reportcolumn', $this->reportRun->replaceSpecialChar($referenceFieldReportColumnSQL));
					}

					$fieldModel->set('reportcolumninfo', $column);

					$fieldModels[] = $fieldModel;
				}
			}
		}
		if ($fieldModels)
			$this->groupByFieldModels = $fieldModels;
	}

	public function getGroupbyColumnsByFieldModel()
	{
		return $this->groupByFieldModels;
	}

	/**
	 * Function returns sql column for group by fields
	 * @param <Array> $selectedfields - field info report format
	 * @return string
	 */
	public function getReportColumnSQL($selectedfields)
	{
		$reportRunObject = $this->getReportRunObject();
		$appendCurrencySymbolToValue = $reportRunObject->append_currency_symbol_to_value;
		$reportRunObject->append_currency_symbol_to_value = [];

		$columnSQL = $reportRunObject->getColumnSQL($selectedfields);

		// Fix for http://code.vtiger.com/vtiger/vtigercrm/issues/4
		switch ($selectedfields[count($selectedfields) - 1]) {
			case 'MY':
				$columnSQL = str_replace('%M', '%m', $columnSQL); // %M (yields Jan), %m - 01
				break;
		}
		// End
		$reportRunObject->append_currency_symbol_to_value = $appendCurrencySymbolToValue;
		return $columnSQL;
	}

	/**
	 * Function returns sql column for data fields
	 * @param <Array> $fieldInfo - field info report format
	 * @return string
	 */
	public function getReportTotalColumnSQL($fieldInfo)
	{
		$primaryModule = $this->getPrimaryModule();
		$columnTotalSQL = $this->getReportRunObject()->getColumnsTotalSQL($fieldInfo, $primaryModule) . ' AS ' . $fieldInfo[2];
		return $columnTotalSQL;
	}

	/**
	 * Function returns labels for aggregate functions
	 * @param type $aggregateFunction
	 * @return string
	 */
	public function getAggregateFunctionLabel($aggregateFunction)
	{
		switch ($aggregateFunction) {
			case 'SUM' : return 'LBL_TOTAL_SUM_OF';
			case 'AVG' : return 'LBL_AVG_OF';
			case 'MIN' : return 'LBL_MIN_OF';
			case 'MAX' : return 'LBL_MAX_OF';
		}
	}

	/**
	 * Function returns translated label for the field from report label
	 * Report label format MODULE_FIELD_LABEL eg:Leads_Lead_Source
	 * @param string $column
	 */
	public function getTranslatedLabelFromReportLabel($column)
	{
		$columnLabelInfo = explode('__', $column);
		$columnLabelInfo = array_diff($columnLabelInfo, array('SUM', 'MIN', 'MAX', 'AVG')); // added to remove aggregate functions from the graph labels
		return vtranslate(implode(' ', array_slice($columnLabelInfo, 1)), $columnLabelInfo[0]);
	}

	/**
	 * Function returns primary module of the report
	 * @return string
	 */
	public function getPrimaryModule()
	{
		$chartModel = $this->getParent();
		$reportModel = $chartModel->getParent();
		$primaryModule = $reportModel->getPrimaryModule();
		return $primaryModule;
	}

	/**
	 * Function returns list view url of the Primary module
	 * @return string
	 */
	public function getBaseModuleListViewURL()
	{
		$primaryModule = $this->getPrimaryModule();
		$primaryModuleModel = Vtiger_Module_Model::getInstance($primaryModule);
		$listURL = $primaryModuleModel->getListViewUrlWithAllFilter();

		return $listURL;
	}

	abstract function generateData();

	public function getQuery()
	{
		$chartModel = $this->getParent();
		$reportModel = $chartModel->getParent();

		$this->reportRun = ReportRun::getInstance($reportModel->getId());
		$advFilterSql = $reportModel->getAdvancedFilterSQL();

		$queryColumnsByFieldModel = $this->getQueryColumnsByFieldModel();

		if (is_array($queryColumnsByFieldModel)) {
			foreach ($queryColumnsByFieldModel as $field) {
				$this->reportRun->queryPlanner->addTable($field->get('table'));
				$columns[] = $field->get('reportcolumn');
			}
		}

		$groupByColumnsByFieldModel = $this->getGroupbyColumnsByFieldModel();

		if (is_array($groupByColumnsByFieldModel)) {
			foreach ($groupByColumnsByFieldModel as $groupField) {
				$this->reportRun->queryPlanner->addTable($groupField->get('table'));
				$groupByColumns[] = $groupField->get('reportlabel');
				$columns[] = $groupField->get('reportcolumn');
			}
		}

		$sql = preg_split('/ from /i', $this->reportRun->sGetSQLforReport($reportModel->getId(), $advFilterSql, 'PDF'));
		$columnLabels = [];

		$chartSQL = 'SELECT ';
		if ($this->isRecordCount()) {
			$chartSQL .= ' count(*) AS RECORD_COUNT,';
		}

		// Add other columns
		if ($columns && is_array($columns)) {
			$columnLabels = array_merge($columnLabels, $groupByColumns);
			$chartSQL .= implode(',', $columns);
		}

		$chartSQL .= " FROM $sql[1] ";

		if ($groupByColumns && is_array($groupByColumns)) {
			$chartSQL .= ' GROUP BY ' . implode(',', $groupByColumns);
		}
		return $chartSQL;
	}

	/**
	 * Function generate links
	 * @param string $field - fieldname
	 * @param <Decimal> $value - value
	 * @return string
	 */
	public function generateLink($field, $value)
	{
		$reportRunObject = $this->getReportRunObject();

		$chartModel = $this->getParent();
		$reportModel = $chartModel->getParent();

		$filter = $reportRunObject->getAdvFilterList($reportModel->getId());

		// Special handling for date fields
		$comparator = 'e';
		$dataFieldInfo = @explode(':', $field);
		if (($dataFieldInfo[4] == 'D' || $dataFieldInfo[4] == 'DT') && !empty($dataFieldInfo[5])) {
			$dataValue = explode(' ', $value);
			if (count($dataValue) > 1) {
				$comparator = 'bw';
				$value = date('Y-m-d H:i:s', strtotime($value)) . ',' . date('Y-m-d', strtotime('last day of' . $value)) . ' 23:59:59';
			} else {
				$comparator = 'bw';
				$value = date('Y-m-d H:i:s', strtotime('first day of JANUARY ' . $value)) . ',' . date('Y-m-d', strtotime('last day of DECEMBER ' . $value)) . ' 23:59:59';
			}
		} elseif ($dataFieldInfo[4] == 'DT') {
			$value = Vtiger_Date_UIType::getDisplayDateTimeValue($value);
		}

		if (empty($value)) {
			$comparator = 'empty';
		}

		//Step 1. Add the filter condition for the field
		$filter[1]['columns'][] = array(
			'columnname' => $field,
			'comparator' => $comparator,
			'value' => $value,
			'column_condition' => ''
		);

		//Step 2. Convert report field format to normal field names
		foreach ($filter as $index => $filterInfo) {
			foreach ($filterInfo['columns'] as $i => $column) {
				if ($column) {
					$fieldInfo = @explode(':', $column['columnname']);
					$filter[$index]['columns'][$i]['columnname'] = $fieldInfo[3];
				}
			}
		}

		//Step 3. Convert advanced filter format to list view search format
		$listSearchParams = array();
		$i = 0;
		if ($filter) {
			foreach ($filter as $index => $filterInfo) {
				foreach ($filterInfo['columns'] as $j => $column) {
					if ($column) {
						$listSearchParams[$i][] = array($column['columnname'], $column['comparator'], $column['value']);
					}
				}
				$i++;
			}
		}
		//Step 4. encode and create the link
		$baseModuleListLink = $this->getBaseModuleListViewURL();
		return $baseModuleListLink . '&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function generates graph label
	 * @return string
	 */
	public function getGraphLabel()
	{
		return $this->getReportModel()->getName();
	}
}

class PieChart extends Base_Chart
{

	public function generateData()
	{
		$db = PearDatabase::getInstance();
		$values = [];
		$chartSQL = $this->getQuery();
		$result = $db->pquery($chartSQL, array());
		$rows = $db->num_rows($result);

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
				$legend = $groupByColumns[] = $groupField->get('reportlabel');
				$legendField = $groupField;
			}
		}

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$currencyRateAndSymbol = \vtlib\Functions::getCurrencySymbolandRate($currentUserModel->currency_id);

		for ($i = 0; $i < $rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
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
				if ($fieldDataType == 'picklist') {
					$label = vtranslate($row[$legend], $legendField->getModuleName());
				} else if ($fieldDataType == 'multipicklist') {
					$multiPicklistValue = $row[$legend];
					$multiPicklistValues = explode(' |##| ', $multiPicklistValue);
					foreach ($multiPicklistValues as $multiPicklistValue) {
						$labelList[] = vtranslate($multiPicklistValue, $legendField->getModuleName());
					}
					$label = implode(',', $labelList);
				} else if ($fieldDataType == 'date') {
					$label = Vtiger_Date_UIType::getDisplayDateValue($row[strtolower($legendField->get('reportlabel'))]);
				} else if ($fieldDataType == 'datetime') {
					$label = Vtiger_Date_UIType::getDisplayDateTimeValue($row[strtolower($legendField->get('reportlabel'))]);
				} else {
					$label = $row[$legend];
				}
			} else {
				$label = $row[$legend];
			}
			$labels[] = (strlen($label) > 30) ? substr($label, 0, 30) . '..' : $label;
			$links[] = $this->generateLink($legendField->get('reportcolumninfo'), $row[strtolower($legend)]);
		}

		$data = array('labels' => $labels,
			'values' => $values,
			'links' => $links,
			'graph_label' => $this->getGraphLabel()
		);
		return $data;
	}
}

class VerticalbarChart extends Base_Chart
{

	public function generateData()
	{
		$db = PearDatabase::getInstance();
		$chartSQL = $this->getQuery();

		$result = $db->pquery($chartSQL, array());
		$rows = $db->num_rows($result);
		$values = array();

		$queryColumnsByFieldModel = $this->getQueryColumnsByFieldModel();

		$recordCountLabel = '';
		if ($this->isRecordCount()) {
			$recordCountLabel = 'RECORD_COUNT';
		}

		$groupByColumnsByFieldModel = $this->getGroupbyColumnsByFieldModel();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$currencyRateAndSymbol = \vtlib\Functions::getCurrencySymbolandRate($currentUserModel->currency_id);
		$links = array();

		for ($i = 0; $i < $rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);

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
					if ($fieldDataType == 'picklist') {
						$label = vtranslate($row[$gFieldModel->get('reportlabel')], $gFieldModel->getModuleName());
					} else if ($fieldDataType == 'multipicklist') {
						$multiPicklistValue = $row[$gFieldModel->get('reportlabel')];
						$multiPicklistValues = explode(' |##| ', $multiPicklistValue);
						foreach ($multiPicklistValues as $multiPicklistValue) {
							$labelList[] = vtranslate($multiPicklistValue, $gFieldModel->getModuleName());
						}
						$label = implode(',', $labelList);
					} else if ($fieldDataType == 'date') {
						$label = Vtiger_Date_UIType::getDisplayDateValue($row[$gFieldModel->get('reportlabel')]);
					} else if ($fieldDataType == 'datetime') {
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

		$data = array('labels' => $labels,
			'values' => $values,
			'links' => $links,
			'type' => (count($values[0]) == 1) ? 'singleBar' : 'multiBar',
			'data_labels' => $this->getDataLabels(),
			'graph_label' => $this->getGraphLabel()
		);
		return $data;
	}

	public function getDataLabels()
	{
		$dataLabels = array();
		if ($this->isRecordCount()) {
			$dataLabels[] = vtranslate('LBL_RECORD_COUNT', 'Reports');
		}
		$queryColumnsByFieldModel = $this->getQueryColumnsByFieldModel();
		if ($queryColumnsByFieldModel) {
			foreach ($queryColumnsByFieldModel as $fieldModel) {
				$fieldTranslatedLabel = $this->getTranslatedLabelFromReportLabel($fieldModel->get('reportlabel'));
				$reportColumn = $fieldModel->get('reportcolumninfo');
				$reportColumnInfo = explode(':', $reportColumn);

				$aggregateFunction = $reportColumnInfo[5];
				$aggregateFunctionLabel = $this->getAggregateFunctionLabel($aggregateFunction);

				$dataLabels[] = vtranslate($aggregateFunctionLabel, 'Reports', $fieldTranslatedLabel);
			}
		}
		return $dataLabels;
	}
}

class HorizontalbarChart extends VerticalbarChart
{
	
}

class LineChart extends VerticalbarChart
{
	
}
