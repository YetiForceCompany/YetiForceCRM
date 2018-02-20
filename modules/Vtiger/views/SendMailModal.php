<?php

/**
 * Send mail modal class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_SendMailModal_View extends Vtiger_BasicModal_View
{
	public $fields = [];

	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!$request->isEmpty('sourceRecord') && !\App\Privilege::isPermitted($request->getByType('sourceModule', 2), 'DetailView', $request->getInteger('sourceRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Pocess function.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$templateModule = $moduleName = $request->getModule();
		$sourceModule = $request->getByType('sourceModule', 2);
		if ($sourceModule && isset(\App\TextParser::$sourceModules[$sourceModule]) && in_array($moduleName, \App\TextParser::$sourceModules[$sourceModule])) {
			$templateModule = $sourceModule;
		}
		$viewer->assign('TEMPLATE_MODULE', $templateModule);
		$viewer->assign('RECORDS', $this->getRecordsListFromRequest($request));
		$viewer->assign('FIELDS', $this->fields);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('SendMailModal.tpl', $moduleName);
		$this->postProcess($request);
	}

	/**
	 * Get records list from request.
	 *
	 * @param \App\Request $request
	 *
	 * @return int[]
	 */
	public function getRecordsListFromRequest(\App\Request $request)
	{
		$dataReader = $this->getQuery($request)->createCommand()->query();
		$count = ['all' => 0, 'emails' => 0];
		foreach ($this->fields as $fieldName => $fieldModel) {
			$count[$fieldName] = 0;
		}
		while ($row = $dataReader->read()) {
			$count['all'] += 1;
			foreach ($this->fields as $fieldName => $fieldModel) {
				if (!empty($row[$fieldName])) {
					$count[$fieldName] += 1;
					$count['emails'] += 1;
				}
			}
		}

		return $count;
	}

	/**
	 * Get query instance.
	 *
	 * @param \App\Request $request
	 *
	 * @return \App\Db\Query
	 */
	public function getQuery(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->getByType('sourceModule', 2);
		if ($sourceModule) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('sourceRecord'), $sourceModule);
			$listView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName);
		} else {
			$listView = Vtiger_ListView_Model::getInstance($moduleName, $request->getByType('viewname', 2));
		}
		$searchResult = $request->get('searchResult');
		if (!empty($searchResult)) {
			$listView->set('searchResult', $searchResult);
		}
		$searchKey = $request->getByType('search_key', 1);
		$searchValue = $request->get('search_value');
		$operator = $request->getByType('operator', 1);
		if (!empty($searchKey) && !empty($searchValue)) {
			$listView->set('operator', $operator);
			$listView->set('search_key', $searchKey);
			$listView->set('search_value', $searchValue);
		}
		$searchParams = $request->get('search_params');
		if (!empty($searchParams) && is_array($searchParams)) {
			$transformedSearchParams = $listView->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams);
			$listView->set('search_params', $transformedSearchParams);
		}
		$queryGenerator = $listView->getQueryGenerator();
		$moduleModel = $queryGenerator->getModuleModel();
		$baseTableName = $moduleModel->get('basetable');
		$baseTableId = $moduleModel->get('basetableid');
		foreach ($moduleModel->getFieldsByType('email') as $fieldName => $fieldModel) {
			if ($fieldModel->isActiveField()) {
				$this->fields[$fieldName] = $fieldModel;
			}
		}
		$queryGenerator->setFields(array_merge(['id'], array_keys($this->fields)));
		$selected = $request->get('selected_ids');
		if ($selected && $selected !== 'all') {
			$queryGenerator->addNativeCondition(["$baseTableName.$baseTableId" => $selected]);
		}
		$excluded = $request->get('excluded_ids');
		if ($excluded) {
			$queryGenerator->addNativeCondition(['not in', "$baseTableName.$baseTableId" => $excluded]);
		}

		return $queryGenerator->createQuery();
	}
}
