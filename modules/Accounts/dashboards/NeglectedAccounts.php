<?php

/**
 * Wdiget to show neglected accounts.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Accounts_NeglectedAccounts_Dashboard extends Vtiger_IndexAjax_View
{
	private $conditions = [];

	/**
	 * Function to get neglected accounts.
	 *
	 * @param string              $moduleName
	 * @param int|array           $user
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return array
	 */
	private function getAccounts($moduleName, $user, Vtiger_Paging_Model $pagingModel)
	{
		$queryGenerator = new App\QueryGenerator($moduleName);
		$queryGenerator->setFields(['id', 'accountname', 'assigned_user_id', 'crmactivity']);
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_entity_stats', 'vtiger_entity_stats.crmid = vtiger_account.accountid']);
		$queryGenerator->addNativeCondition(['or', ['<=', 'vtiger_entity_stats.crmactivity', 0], ['vtiger_entity_stats.crmactivity' => null]]);
		$queryGenerator->addCondition('assigned_user_id', $user, 'e');
		$queryGenerator->setLimit($pagingModel->getPageLimit());
		$queryGenerator->setOffset($pagingModel->getStartIndex());
		$dataReader = $queryGenerator->createQuery()->orderBy(new yii\db\Expression('vtiger_entity_stats.crmactivity IS NULL'))
			->addOrderBy(['vtiger_entity_stats.crmactivity' => SORT_ASC])->createCommand()->query();
		$accounts = [];
		while ($row = $dataReader->read()) {
			$accounts[$row['id']] = \Vtiger_Module_Model::getInstance($moduleName)->getRecordFromArray($row);
		}
		$dataReader->close();
		$this->conditions = [
			'condition' => ['or', ['vtiger_entity_stats.crmactivity' => null], ['<', 'vtiger_entity_stats.crmactivity', 0]],
			'join' => [['LEFT JOIN', 'vtiger_entity_stats', 'vtiger_entity_stats.crmid = vtiger_crmentity.crmid']],
		];

		return $accounts;
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$currentUser = \App\User::getCurrentUserModel();
		$moduleName = $request->getModule();
		$linkId = $request->getInteger('linkid');
		$user = $request->getByType('owner', 2);
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (empty($user)) {
			$user = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		}
		$accessibleUsers = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleUsersForModule();
		$accessibleGroups = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleGroupForModule();
		if ('all' == $user) {
			$user = array_keys($accessibleUsers);
		}
		$page = $request->getInteger('page');
		if (empty($page)) {
			$page = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));
		$accounts = $this->getAccounts($moduleName, $user, $pagingModel);
		$viewer = $this->getViewer($request);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('ACCOUNTS', $accounts);
		$viewer->assign('OWNER', $user);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('USER_CONDITIONS', $this->conditions);
		if ($request->has('content')) {
			$viewer->view('dashboards/NeglectedAccountsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/NeglectedAccounts.tpl', $moduleName);
		}
	}
}
