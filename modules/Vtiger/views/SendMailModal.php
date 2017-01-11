<?php

/**
 * Send mail modal class
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_SendMailModal_View extends Vtiger_BasicModal_View
{

	public $fields = [];

	/**
	 * Checking permissions
	 * @param Vtiger_Request $request
	 * @throws \Exception\AppException
	 * @throws \Exception\NoPermittedToRecord
	 */
	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($moduleName)) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
		if (!$request->isEmpty('sourceRecord') && !\App\Privilege::isPermitted($request->get('sourceModule'), 'DetailView', $request->get('sourceRecord'))) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	/**
	 * Pocess function
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$templateModule = $moduleName = $request->getModule();
		$sourceModule = $request->get('sourceModule');
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
	 * Get records list from request
	 * @param Vtiger_Request $request
	 * @return int[]
	 */
	public function getRecordsListFromRequest(Vtiger_Request $request)
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
	 * Get query instance
	 * @param Vtiger_Request $request
	 * @return \App\Db\Query
	 */
	public function getQuery(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->get('sourceModule');
		if ($sourceModule) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->get('sourceRecord'), $sourceModule);
			$listView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName);
		} else {
			$listView = Vtiger_ListView_Model::getInstance($moduleName, $request->get('viewname'));
		}
		$searchResult = $request->get('searchResult');
		if (!empty($searchResult)) {
			$listView->set('searchResult', $searchResult);
		}
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');
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
