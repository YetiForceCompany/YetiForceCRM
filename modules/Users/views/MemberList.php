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
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$groupId = $request->getInteger('groupID');
		$groups = \App\PrivilegeUtil::getMembers();
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
