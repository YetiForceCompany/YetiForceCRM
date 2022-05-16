<?php
/**
 * Export data file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Export data class.
 */
class Users_ExportData_Action extends Vtiger_ExportData_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$this->moduelName = $request->getModule();
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->exportModel = \App\Export\Records::getInstance($this->moduelName, 'csv')
			->setLimit(\App\Config::performance('MAX_NUMBER_EXPORT_RECORDS'))
			->setFormat(\App\Export\Records::EXPORT_FORMAT);
		$this->exportModel->fullData = true;

		$this->setDataFromRequest($request);
		$this->exportModel->sendHttpHeader();
		$this->exportModel->exportData();
	}

	/** {@inheritdoc} */
	public function setDataFromRequest(App\Request $request)
	{
		$searchParams = \App\Condition::validSearchParams($this->moduelName, $request->getArray('search_params'));
		$operator = $request->isEmpty('operator') ? '' : $request->getByType('operator');
		if ($operator && $searchValue = \App\Condition::validSearchValue($request->getByType('search_value', \App\Purifier::TEXT), $this->moduelName, $request->getByType('search_key', \App\Purifier::ALNUM), $operator)) {
			$searchKey = $request->getByType('search_key', \App\Purifier::ALNUM);
		}

		$queryGenerator = $this->exportModel->getQueryGenerator();
		$queryGenerator->addCondition('status', 'Active', 'e');

		switch ($request->getMode()) {
				case 'ExportAllData':
					break;
				case 'ExportSelectedRecords':
					$selectedIds = $request->getArray('selected_ids', \App\Purifier::ALNUM);
					if ($selectedIds && 'all' !== $selectedIds[0]) {
						$queryGenerator->addCondition('id', $selectedIds, 'e');
					}
					$queryGenerator->parseAdvFilter($queryGenerator->parseBaseSearchParamsToCondition($searchParams));
					if ($excludedIds = $request->getArray('excluded_ids', \App\Purifier::INTEGER)) {
						$queryGenerator->addCondition('id', $excludedIds, 'n');
					}
					if ($operator && $searchValue && $searchKey) {
						$queryGenerator->addCondition($searchKey, $searchValue, $operator);
					}
					break;
				default:
					throw new \App\Exceptions\IllegalValue('ERR_FIELD_NOT_FOUND||mode');
			}
	}
}
