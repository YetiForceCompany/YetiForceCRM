<?php

/**
 * Wdiget to show new accounts
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Accounts_NewAccounts_Dashboard extends Vtiger_IndexAjax_View
{

	private function getAccounts($moduleName, $user, $time, $pagingModel)
	{
		$time['start'] = DateTimeField::convertToDBFormat($time['start']);
		$time['end'] = DateTimeField::convertToDBFormat($time['end']);
		$time['start'] .= ' 00:00:00';
		$time['end'] .= ' 23:59:59';
		$sql = 'SELECT vtiger_crmentity.crmid ,vtiger_account.accountname, vtiger_crmentity.smownerid,	vtiger_crmentity.createdtime 
			FROM vtiger_account
			INNER JOIN vtiger_crmentity ON vtiger_account.accountid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.setype = ? AND vtiger_crmentity.createdtime >= ? AND vtiger_crmentity.createdtime <= ? AND vtiger_crmentity.deleted = ?';
		$params = [$moduleName, $time['start'], $time['end'], 0];
		if (is_array($user)) {
			$sql .= ' AND vtiger_crmentity.smownerid IN (' . generateQuestionMarks($user) . ') ';
			$params = array_merge($params, $user);
		} else {
			$sql .= ' AND vtiger_crmentity.smownerid = ? ';
			$params[] = $user;
		}
		$sql.= \App\PrivilegeQuery::getAccessConditions($moduleName);
		$sql .= ' ORDER BY  vtiger_crmentity.createdtime DESC LIMIT ? OFFSET ?';
	
		$params[] = $pagingModel->getPageLimit();
		$params[] = $pagingModel->getStartIndex();
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, $params);
		$newAccounts = [];
		while ($row = $db->getRow($result)) {
			$row['userModel'] = Users_Privileges_Model::getInstanceById($row['smownerid']);
			$time = new DateTimeField($row['createdtime']);
			$row['createdtime'] = $time->getFullcalenderDateTimevalue();
			$newAccounts[$row['crmid']] = $row;
		}
		return $newAccounts;
	}

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$user = $request->get('owner');
		$time = $request->get('time');
		if (empty($time)) {
			$time['start'] = vtlib\Functions::currentUserDisplayDateNew();
			$time['end'] = vtlib\Functions::currentUserDisplayDateNew();
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
		$page = $request->get('page');
		if (empty($page)) {
			$page = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));
		$newAccounts = $this->getAccounts($moduleName, $user, $time, $pagingModel);
		$viewer = $this->getViewer($request);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('NEW_ACCOUNTS', $newAccounts);
		$viewer->assign('DTIME', $time);
		$viewer->assign('OWNER', $user);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/NewAccountsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/NewAccounts.tpl', $moduleName);
		}
	}
}
