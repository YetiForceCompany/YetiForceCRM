<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Vtiger_BasicAjax_View extends \App\Controller\View\Page
{
	use \App\Controller\ClearProcess;
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showAdvancedSearch');
		$this->exposeMethod('showSearchResults');
		$this->exposeMethod('performPhoneCall');
		$this->exposeMethod('getDashBoardPredefinedWidgets');
	}

	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule()) && ($request->isEmpty('parent', true) || 'Settings' !== $request->getByType('parent', 2) || !$currentUserPrivilegesModel->isAdminUser())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('searchModule') && '-' !== $request->getRaw('searchModule') && !$currentUserPrivilegesModel->hasModulePermission($request->getByType('searchModule', 2))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function to display the UI for advance search on any of the module.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function showAdvancedSearch(App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->getRoleInstance()->get('globalsearchadv')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if (!$request->isEmpty('searchModule') && '-' !== $request->getRaw('searchModule')) {
			$moduleName = $request->getByType('searchModule', 2);
		} elseif (false === \App\Module::getModuleId($moduleName) || (!$request->isEmpty('parent', true) && 'Settings' === $request->getByType('parent', 2))) {
			//See if it is an excluded module, If so search in home module
			$moduleName = 'Home';
		}
		$saveFilterPermitted = true;
		if (\in_array($moduleName, ['ModComments', 'RSS', 'Portal', 'Integration', 'DashBoard'])) {
			$saveFilterPermitted = false;
		}
		//See if it is an excluded module, If so search in home module
		if ('Vtiger' === $moduleName) {
			$moduleName = 'Home';
		}
		$module = $request->getModule();
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($moduleName)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$viewer->assign('SEARCHABLE_MODULES', \App\RecordSearch::getSearchableModules());
		$viewer->assign('SOURCE_MODULE', $moduleName);
		$viewer->assign('MODULE', $module);
		$viewer->assign('SAVE_FILTER_PERMITTED', $saveFilterPermitted);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		echo $viewer->view('AdvanceSearch.tpl', $moduleName, true);
	}

	/**
	 * Function to display the Search Results.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function showSearchResults(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$advFilterList = $request->getArray('advfilterlist', 'Text');
		//used to show the save modify filter option
		$isAdvanceSearch = false;
		$matchingRecords = [];
		if (\is_array($advFilterList) && $advFilterList) {
			if (!\App\User::getCurrentUserModel()->getRoleInstance()->get('globalsearchadv')) {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
			$isAdvanceSearch = true;
			$queryGenerator = new \App\QueryGenerator($moduleName);
			$queryGenerator->setFields(['id']);
			$queryGenerator->setConditions(\App\Condition::getConditionsFromRequest($advFilterList));
			$query = $queryGenerator->createQuery()->limit(App\Config::search('GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT'));
			$dataReader = $query->createCommand()->query();
			while ($recordId = $dataReader->readColumn(0)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
				$recordModel->set('permitted', true);
				$matchingRecords[$moduleName][$recordId] = $recordModel;
			}
			$viewer->assign('SEARCH_MODULE', $moduleName);
		} else {
			$searchKey = \App\RecordSearch::getSearchField()->getUITypeModel()->getDbConditionBuilderValue($request->getByType('value', 'Text'), '');
			$limit = ($request->isEmpty('limit', true) || \App\Validator::bool($request->get('limit'))) ? null : $request->getInteger('limit');
			$operator = (!$request->isEmpty('operator')) ? $request->getByType('operator', \App\Purifier::STANDARD) : null;
			$searchModule = null;
			if (!$request->isEmpty('searchModule', true) && '-' !== $request->getRaw('searchModule')) {
				$searchModule = $request->getByType('searchModule', \App\Purifier::ALNUM);
			}
			$viewer->assign('SEARCH_MODULE', $searchModule);
			$matchingRecords = \App\RecordSearch::getSearchResult($searchKey, $searchModule, $limit, $operator);
			if (1 === App\Config::search('GLOBAL_SEARCH_SORTING_RESULTS')) {
				$matchingRecordsList = [];
				foreach (\App\Module::getAllEntityModuleInfo(true) as $module) {
					if (isset($matchingRecords[$module['modulename']]) && 1 == $module['turn_off']) {
						$matchingRecordsList[$module['modulename']] = $matchingRecords[$module['modulename']];
					}
				}
				$matchingRecords = $matchingRecordsList;
			}
		}
		if (App\Config::search('GLOBAL_SEARCH_CURRENT_MODULE_TO_TOP') && isset($matchingRecords[$moduleName])) {
			$pushTop = $matchingRecords[$moduleName];
			unset($matchingRecords[$moduleName]);
			$matchingRecords = [$moduleName => $pushTop] + $matchingRecords;
		}
		if ($request->getBoolean('html')) {
			$viewer->assign('MODULE', $moduleName);
			$viewer->assign('MATCHING_RECORDS', $matchingRecords);
			$viewer->assign('IS_ADVANCE_SEARCH', $isAdvanceSearch);
			echo $viewer->view('UnifiedSearchResults.tpl', '', true);
		} else {
			$recordsList = [];
			foreach ($matchingRecords as $module => &$modules) {
				foreach ($modules as $recordID => $recordModel) {
					$label = $recordModel->getName();
					$label .= ' (' . \App\Fields\Owner::getLabel($recordModel->get('assigned_user_id')) . ')';
					if (!$recordModel->get('permitted')) {
						$label .= ' <span class="fas fa-exclamation-circle" aria-hidden="true"></span>';
					}
					$recordsList[] = [
						'id' => $recordID,
						'module' => $module,
						'category' => \App\Language::translate($module, $module),
						'label' => $label,
						'permitted' => $recordModel->get('permitted'),
					];
				}
			}
			$response = new Vtiger_Response();
			$response->setResult($recordsList);
			$response->emit();
		}
	}

	/**
	 * Perform phone call.
	 *
	 * @param \App\Request $request
	 */
	public function performPhoneCall(App\Request $request)
	{
		$pbx = App\Integrations\Pbx::getDefaultInstance();
		$pbx->loadUserPhone();
		try {
			$pbx->performCall($request->getByType('phoneNumber', 'Phone'));
			$response = new Vtiger_Response();
			$response->setResult(\App\Language::translate('LBL_PHONE_CALL_SUCCESS'));
			$response->emit();
		} catch (Exception $exc) {
			\App\Log::error('Error while telephone connections: ' . $exc->getMessage(), 'PBX');
		}
	}

	/**
	 * Return button of predefined widgets.
	 *
	 * @param \App\Request $request
	 */
	public function getDashBoardPredefinedWidgets(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$dashBoardModel->set('dashboardId', $request->getInteger('dashboardId'));
		$dashBoardModel->verifyDashboard($moduleName);
		$widgets = $dashBoardModel->getDashboards(0);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->view('dashboards/DashBoardWidgetsList.tpl', $moduleName);
	}
}
