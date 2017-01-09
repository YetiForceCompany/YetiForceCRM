<?php

/**
 * Mail action class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Mail_Action extends Vtiger_Action_Controller
{

	/**
	 * Checking permissions
	 * @param Vtiger_Request $request
	 * @return boolean
	 */
	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		if (!\App\Privilege::isPermitted($moduleName)) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
		if (!$request->isEmpty('sourceRecord') && !\App\Privilege::isPermitted($request->get('sourceModule'), 'DetailView', $request->get('sourceRecord'))) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('checkSmtp');
		$this->exposeMethod('sendMails');
	}

	/**
	 * Process function
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Check if smtps are active
	 * @param Vtiger_Request $request
	 */
	public function checkSmtp(Vtiger_Request $request)
	{
		$result = false;
		if (AppConfig::main('isActiveSendingMails')) {
			$result = !empty(App\Mail::getAll());
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Send mails
	 * @param Vtiger_Request $request
	 */
	public function sendMails(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$field = $request->get('field');
		$template = $request->get('template');
		$sourceModule = $request->get('sourceModule');
		$sourceRecord = $request->get('sourceRecord');
		$result = false;
		if (!empty($template) && !empty($field)) {
			$dataReader = $this->getQuery($request)->createCommand()->query();
			while ($row = $dataReader->read()) {
				$result = \App\Mailer::sendFromTemplate([
						'template' => $template,
						'moduleName' => $moduleName,
						'recordId' => $row['id'],
						'to' => $row[$field],
						'sourceModule' => $sourceModule,
						'sourceRecord' => $sourceRecord,
				]);
				if (!$result) {
					break;
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
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
		$queryGenerator->setFields(['id', $request->get('field')]);
		$queryGenerator->addCondition($request->get('field'), '', 'ny');
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
