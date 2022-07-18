<?php

/**
 * Lider member list view file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Lider member list view class.
 */
class Users_MemberList_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $successBtn = 'LBL_ADD';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-plus';

	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_GROUP_MEMBERS_ADD_VIEW';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$groupId = $request->getInteger('groupID', 0);
		$userModel = \App\User::getCurrentUserModel();

		if (!$groupId || !\in_array($groupId, $userModel->get('leader')) || !\App\Privilege::isPermitted($moduleName, 'LeaderCanManageGroupMembership')) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$userModel = \App\User::getCurrentUserModel();
		$groupId = $request->getInteger('groupID');
		if ($userModel->isAdmin()) {
			$groups = \App\PrivilegeUtil::getMembers();
		} else {
			$groups[\App\PrivilegeUtil::MEMBER_TYPE_USERS][\App\PrivilegeUtil::MEMBER_TYPE_USERS . ':' . $userModel->getId()] = ['name' => $userModel->getName(), 'id' => $userModel->getId(), 'type' => \App\PrivilegeUtil::MEMBER_TYPE_USERS];
			$groups[\App\PrivilegeUtil::MEMBER_TYPE_ROLES] = [];
			$groups[\App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES] = [];
			foreach (\App\User::getPrivilegesFile($userModel->getId())['subordinate_roles_users'] as $roleId => $users) {
				$roleName = \App\Labels::role($roleId);
				$groups[\App\PrivilegeUtil::MEMBER_TYPE_ROLES][\App\PrivilegeUtil::MEMBER_TYPE_ROLES . ':' . $roleId] = ['name' => $roleName, 'id' => $roleId, 'type' => \App\PrivilegeUtil::MEMBER_TYPE_ROLES];
				$groups[\App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES][\App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES . ':' . $roleId] = ['name' => $roleName, 'id' => $roleId, 'type' => \App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES];
				foreach ($users as $userId => $userName) {
					if (\App\User::isExists($userId)) {
						$groups[\App\PrivilegeUtil::MEMBER_TYPE_USERS][\App\PrivilegeUtil::MEMBER_TYPE_USERS . ':' . $userId] = ['name' => $userName, 'id' => $userId, 'type' => \App\PrivilegeUtil::MEMBER_TYPE_USERS];
					}
				}
			}
		}

		$groupMembers = array_flip(Settings_Groups_Member_Model::getAllByTypeForGroup($groupId));
		$groupMembers[\App\PrivilegeUtil::MEMBER_TYPE_GROUPS . ":{$groupId}"] = 0;
		foreach ($groups as &$members) {
			$members = array_diff_key($members, $groupMembers);
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('GROUP_ID', $groupId);
		$viewer->assign('GROUPS', $groups);
		$viewer->view('MemberList.tpl', $request->getModule());
	}
}
