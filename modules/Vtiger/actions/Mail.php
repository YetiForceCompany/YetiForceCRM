<?php

/**
 * Mail action class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Mail_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		if (!$request->isEmpty('sourceRecord') && !\App\Privilege::isPermitted($request->getByType('sourceModule', 2), 'DetailView', $request->getInteger('sourceRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('checkSmtp');
		$this->exposeMethod('sendMails');
	}

	/**
	 * Check if smtps are active.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function checkSmtp(App\Request $request): void
	{
		$result = false;
		if (App\Config::main('isActiveSendingMails')) {
			$result = !empty(App\Mail::getAll());
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Send mails.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function sendMails(App\Request $request): void
	{
		$moduleName = $request->getModule();
		$field = $request->getByType('field', 'Alnum');
		$sourceModule = $request->getByType('sourceModule', 'Alnum');
		$sourceRecord = $request->getInteger('sourceRecord');
		$result = false;
		if (!$request->isEmpty('template') && !empty($field)) {
			$params = [
				'template' => $request->getInteger('template'),
				'massMailNotes' => $request->getForHtml('mailNotes'),
			];
			$emails = [];
			foreach ($this->getQuery($request)->each() as $row) {
				if (isset($emails[$row[$field]])) {
					$emails[$row[$field]][] = $row['id'];
				} else {
					$emails[$row[$field]] = [$row['id']];
				}
			}
			foreach ($emails as $email => $ids) {
				$id = current($ids);
				if (isset(\App\TextParser::$sourceModules[$sourceModule]) && \in_array($moduleName, \App\TextParser::$sourceModules[$sourceModule])) {
					$extraParams = [
						'moduleName' => $sourceModule,
						'recordId' => $sourceRecord,
						'sourceModule' => $moduleName,
						'sourceRecord' => $id,
					];
				} else {
					$extraParams = [
						'moduleName' => $moduleName,
						'recordId' => $id,
						'sourceModule' => $sourceModule,
						'sourceRecord' => $sourceRecord,
					];
				}
				$params['to'] = $email;
				$params['emailIds'] = $ids;
				$result = \App\Mailer::sendFromTemplate(array_merge($params, $extraParams));
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
	 * Get query instance.
	 *
	 * @param \App\Request $request
	 *
	 * @return \App\Db\Query
	 */
	public function getQuery(App\Request $request): App\Db\Query
	{
		$moduleName = $request->getModule();
		$sourceModule = $request->getByType('sourceModule', 2);
		if ($sourceModule) {
			$cvId = $request->isEmpty('cvId', true) ? 0 : $request->getByType('cvId', 'Alnum');
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('sourceRecord'), $sourceModule);
			$listView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $moduleName, $request->getInteger('relationId'), $cvId);
		} else {
			$listView = Vtiger_ListView_Model::getInstance($moduleName, $request->getByType('viewname', 2));
		}
		if (!$request->isEmpty('searchResult', true)) {
			$listView->set('searchResult', $request->getArray('searchResult', 'Integer'));
		}
		$searchKey = $request->getByType('search_key');
		$operator = $request->getByType('operator');
		$searchValue = $request->getByType('search_value', 'Text');
		if (!empty($searchKey) && !empty($searchValue)) {
			$listView->set('operator', $operator);
			$listView->set('search_key', $searchKey);
			$listView->set('search_value', App\Condition::validSearchValue($searchValue, $listView->getQueryGenerator()->getModule(), $searchKey, $operator));
		}
		$searchParams = App\Condition::validSearchParams($listView->getQueryGenerator()->getModule(), $request->getArray('search_params'));
		if (!empty($searchParams) && \is_array($searchParams)) {
			$transformedSearchParams = $listView->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams);
			$listView->set('search_params', $transformedSearchParams);
		}
		if ($advancedConditions = $request->has('advancedConditions') ? $request->getArray('advancedConditions') : []) {
			$listView->set('advancedConditions', \App\Condition::validAdvancedConditions($advancedConditions));
		}
		if ($sourceModule) {
			$queryGenerator = $listView->getRelationQuery(true);
		} else {
			$listView->loadListViewCondition();
			$queryGenerator = $listView->getQueryGenerator();
		}
		$moduleModel = $queryGenerator->getModuleModel();
		$baseTableName = $moduleModel->get('basetable');
		$baseTableId = $moduleModel->get('basetableid');
		$queryGenerator->setFields(['id', $request->getByType('field', 'Alnum')]);
		$queryGenerator->addCondition($request->getByType('field', 'Alnum'), '', 'ny');
		$selected = $request->getArray('selected_ids', 2);
		if ($selected && 'all' !== $selected[0]) {
			$queryGenerator->addNativeCondition(["$baseTableName.$baseTableId" => $selected]);
		}
		$excluded = $request->getArray('excluded_ids', 2);
		if ($excluded) {
			$queryGenerator->addNativeCondition(['not in', "$baseTableName.$baseTableId" => $excluded]);
		}
		return $queryGenerator->createQuery();
	}
}
