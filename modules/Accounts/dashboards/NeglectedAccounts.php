<?php

/**
 * Wdiget to show neglected accounts
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Accounts_NeglectedAccounts_Dashboard extends Vtiger_IndexAjax_View
{

	private $conditions = [];

	private function getAccounts($moduleName, $user, $pagingModel)
	{
		$sql = 'SELECT vtiger_crmentity.crmid ,vtiger_account.accountname, vtiger_crmentity.smownerid,	vtiger_entity_stats.crmactivity 
			FROM vtiger_account
			INNER JOIN vtiger_crmentity ON vtiger_account.accountid = vtiger_crmentity.crmid
			INNER JOIN vtiger_entity_stats ON vtiger_entity_stats.crmid = vtiger_account.accountid
			WHERE vtiger_crmentity.setype = ? AND vtiger_crmentity.deleted = ? AND 
			(vtiger_entity_stats.crmactivity <= ? OR vtiger_entity_stats.crmactivity IS NULL)';
		$params = [$moduleName, 0, 0];
		if (is_array($user)) {
			$sql .= ' AND vtiger_crmentity.smownerid IN (' . generateQuestionMarks($user) . ') ';
			$params = array_merge($params, $user);
		} else {
			$sql .= ' AND vtiger_crmentity.smownerid = ? ';
			$params[] = $user;
		}
		$sql.= \App\PrivilegeQuery::getAccessConditions($moduleName);
		$sql .= ' ORDER BY vtiger_entity_stats.crmactivity IS NULL, vtiger_entity_stats.crmactivity  ASC  LIMIT ? OFFSET ?';
		$params[] = $pagingModel->getPageLimit();
		$params[] = $pagingModel->getStartIndex();
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, $params);
		$accounts = [];
		while ($row = $db->getRow($result)) {
			$row['userModel'] = Users_Privileges_Model::getInstanceById($row['smownerid']);
			$accounts[$row['crmid']] = $row;
		}
		$this->conditions = [
			'condition' => ['or', ['vtiger_entity_stats.crmactivity' => null], ['<', 'vtiger_entity_stats.crmactivity', 0]],
			'join' => [['LEFT JOIN', 'vtiger_entity_stats', 'vtiger_entity_stats.crmid = vtiger_crmentity.crmid']]
		];
		return $accounts;
	}

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$user = $request->get('owner');
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (empty($user)) {
			$user = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		}
		$accessibleUsers = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleUsersForModule();
		$accessibleGroups = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleGroupForModule();
		if ($user == 'all') {
			$user = array_keys($accessibleUsers);
		}
		$page = $request->get('page');
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
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('USER_CONDITIONS', $this->conditions);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/NeglectedAccountsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/NeglectedAccounts.tpl', $moduleName);
		}
	}
}
