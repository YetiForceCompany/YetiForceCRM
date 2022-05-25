<?php
/**
 * Export data file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Export data class.
 */
class Vtiger_ExportData_Action extends Vtiger_Mass_Action
{
	/** @var string Module name */
	protected $moduelName;
	/** @var \App\Export\ExportRecords Export model instance */
	protected $exportModel;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$this->moduelName = $request->getModule();
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModuleActionPermission($this->moduelName, 'Export')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function is called by the controller.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$this->exportModel = \App\Export\Records::getInstance($this->moduelName, $request->getByType('export_type', \App\Purifier::ALNUM))
			->setLimit(\App\Config::performance('MAX_NUMBER_EXPORT_RECORDS'))
			->setFormat(\App\Export\Records::EXPORT_FORMAT);
		$this->exportModel->fullData = true;

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
		$cvId = $request->getInteger('viewname');
		$searchParams = \App\Condition::validSearchParams($this->moduelName, $request->getArray('search_params'));
		$operator = $request->isEmpty('operator') ? '' : $request->getByType('operator');
		if ($operator && $searchValue = \App\Condition::validSearchValue($request->getByType('search_value', \App\Purifier::TEXT), $this->moduelName, $request->getByType('search_key', \App\Purifier::ALNUM), $operator)) {
			$searchKey = $request->getByType('search_key', \App\Purifier::ALNUM);
		}
		$queryGenerator = $this->exportModel->getQueryGenerator();
		$queryGenerator->setStateCondition($request->getByType('entityState'));
		$queryGenerator->initForCustomViewById($cvId);
		if ($advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : []) {
			$queryGenerator->setAdvancedConditions(\App\Condition::validAdvancedConditions($advancedConditions));
		}

		switch ($request->getMode()) {
				case 'ExportAllData':
					break;
				case 'ExportCurrentPage':
					$exportLimit = \App\Config::performance('MAX_NUMBER_EXPORT_RECORDS');
					$pagingModel = new \Vtiger_Paging_Model();
					$limit = $pagingModel->getPageLimit();
					if ($limit > $exportLimit) {
						$limit = $exportLimit;
					}
					$currentPage = $request->getInteger('page', 1) ?: 1;
					$offset = ($currentPage - 1) * $limit;
					$this->exportModel->setLimit($limit);
					$queryGenerator
						->setLimit($limit)
						->setOffset($offset)
						->parseAdvFilter($queryGenerator->parseBaseSearchParamsToCondition($searchParams));
					if ($operator && $searchValue && $searchKey) {
						$queryGenerator->addCondition($searchKey, $searchValue, $operator);
					}
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
