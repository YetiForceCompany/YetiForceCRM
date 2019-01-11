<?php

/**
 * Wdiget to show new accounts.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Accounts_NewAccounts_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Function to get the newest accounts.
	 *
	 * @param string              $moduleName
	 * @param int|array           $user
	 * @param array               $time
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return array
	 */
	private function getAccounts($moduleName, $user, $time, Vtiger_Paging_Model $pagingModel)
	{
		$queryGenerator = new App\QueryGenerator($moduleName);
		$queryGenerator->setFields(['id', 'accountname', 'assigned_user_id', 'createdtime']);
		$queryGenerator->addCondition('assigned_user_id', $user, 'e');
		$queryGenerator->addCondition('createdtime', implode(',', $time), 'bw');
		$queryGenerator->setLimit($pagingModel->getPageLimit());
		$queryGenerator->setOffset($pagingModel->getStartIndex());
		$queryGenerator->setOrder('createdtime', 'DESC');
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$newAccounts = [];
		while ($row = $dataReader->read()) {
			$row['userModel'] = Users_Privileges_Model::getInstanceById($row['assigned_user_id']);
			$newAccounts[$row['id']] = $row;
		}
		$dataReader->close();

		return $newAccounts;
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$currentUser = \App\User::getCurrentUserModel();
		$moduleName = $request->getModule();
		$linkId = $request->getInteger('linkid');
		$user = $request->getByType('owner', 2);
		$time = $request->getByType('time', 'DateRangeUserFormat');
		if (empty($time)) {
			$time['start'] = App\Fields\Date::formatToDisplay('now');
			$time['end'] = App\Fields\Date::formatToDisplay('now');
		} else {
			foreach ($time as &$timeValue) {
				$timeValue = App\Fields\Date::formatToDisplay($timeValue);
			}
		}
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (empty($user)) {
			$user = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		}
		$accessibleUsers = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleUsersForModule();
		$accessibleGroups = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleGroupForModule();
		if ($user == 'all') {
			$user = array_keys($accessibleUsers);
		}
		$page = $request->getInteger('page');
		if (empty($page)) {
			$page = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));
		$viewer = $this->getViewer($request);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('NEW_ACCOUNTS', $this->getAccounts($moduleName, $user, $time, $pagingModel));
		$viewer->assign('DTIME', $time);
		$viewer->assign('OWNER', $user);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		if ($request->has('content')) {
			$viewer->view('dashboards/NewAccountsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/NewAccounts.tpl', $moduleName);
		}
	}
}
