<?php
/**
 * Quick export data file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Quick export data class.
 */
class Vtiger_QuickExportData_Action extends Vtiger_Mass_Action
{
	/** @var string Module name */
	protected $moduelName;
	/** @var \App\Export\Records Export model instance */
	protected $exportModel;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$this->moduelName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModuleActionPermission($this->moduelName, 'QuickExportToExcel')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->exportModel = \App\Export\Records::getInstance($this->moduelName, $request->getByType('export_type', \App\Purifier::ALNUM))
			->setLimit(\App\Config::performance('MAX_NUMBER_EXPORT_RECORDS'))
			->setFormat(\App\Export\Records::USER_FORMAT);

		$this->setDataFromRequest($request);
		$this->exportModel->sendHttpHeader();
		$this->exportModel->exportData();
	}

	/**
	 * Set condition data in export model.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function setDataFromRequest(App\Request $request)
	{
		if ($request->has('xmlExportType')) {
			$this->exportModel->setTemplate($request->getByType('xmlExportType', 'Text'));
		}
		$queryGenerator = $this->exportModel->getQueryGenerator();

		$cvId = $request->getInteger('viewname');
		$queryGenerator->initForCustomViewById($cvId);

		$selectedIds = $request->getArray('selected_ids', \App\Purifier::ALNUM);
		if ($selectedIds && 'all' !== $selectedIds[0]) {
			$queryGenerator->addCondition('id', $selectedIds, 'e');
		}
		if ($advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : []) {
			$queryGenerator->setAdvancedConditions(\App\Condition::validAdvancedConditions($advancedConditions));
		}
		$searchParams = \App\Condition::validSearchParams($this->moduelName, $request->getArray('search_params'));
		if ($searchParams) {
			$transformedSearchParams = $queryGenerator->parseBaseSearchParamsToCondition($searchParams);
			$queryGenerator->parseAdvFilter($transformedSearchParams);
		}
		$operator = $request->isEmpty('operator') ? '' : $request->getByType('operator');
		if ($operator && $searchValue = \App\Condition::validSearchValue($request->getByType('search_value', \App\Purifier::TEXT), $this->moduelName, $request->getByType('search_key', \App\Purifier::ALNUM), $operator)) {
			$queryGenerator->addCondition($request->getByType('search_key', \App\Purifier::ALNUM), $searchValue, $operator);
		}
		$queryGenerator->setStateCondition($request->getByType('entityState'));

		if ($excludedIds = $request->getArray('excluded_ids', \App\Purifier::INTEGER)) {
			$queryGenerator->addCondition('id', $excludedIds, 'n');
		}

		if (!$request->isEmpty('exportColumns', true) && $fields = $request->getArray('exportColumns', \App\Purifier::TEXT)) {
			$this->exportModel->setFields($fields);
		} else {
			$fields = \App\CustomView::getInstance($this->moduelName)->getColumnsListByCvid($cvId);
			array_walk($fields, function (&$fieldInfo) {
				['field_name' => $relatedFieldName, 'module_name' => $relatedModule, 'source_field_name' => $referenceField] = $fieldInfo;
				$fieldInfo = $referenceField ? "{$relatedFieldName}:{$relatedModule}:{$referenceField}" : $relatedFieldName;
			});
			$this->exportModel->setFields($fields);
		}
	}
}
