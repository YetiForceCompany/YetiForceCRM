<?php

/**
 * Lider group action file.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Lider group action class.
 */
class Users_Groups_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;
	use \App\Controller\Traits\SettingsPermission;

	/** @var array Subordinate roles/users */
	private $subordinates;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getData');
		$this->exposeMethod('removeMember');
		$this->exposeMethod('addMembers');
	}

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

	/**
	 * Gets data for DataTable element.
	 *
	 * @param App\Request $request
	 */
	public function getData(App\Request $request)
	{
		$moduleName = $request->getModule();
		$groupId = $request->getInteger('groupID');
		$groupMembers = Settings_Groups_Member_Model::getAllByTypeForGroup($groupId);
		$count = \count($groupMembers);
		$rows = [];

		foreach ($groupMembers as $member) {
			$label = \App\Labels::member($member);
			[$type, $id] = explode(':', $member);
			if (\App\PrivilegeUtil::MEMBER_TYPE_USERS === $type && !\App\User::isExists($id)) {
				$label = "<del>{$label}</del>";
			}
			$data = [$label];
			if ($count > 1 && $this->isMemberEditable($member)) {
				$data[] = '<button type="button" class="btn btn-danger btn-sm js-member-delete" data-id="' . $member . '" title="' . \App\Language::translate('LBL_DELETE') . '" data-url="' . "index.php?&module={$moduleName}&action=Groups&mode=removeMember&groupID={$groupId}&member={$member}" . '"><span class="fas fa-trash-alt"></span></button>';
			} else {
				$data[] = '';
			}
			$rows[] = $data;
		}
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalDisplayRecords' => \count($groupMembers),
			'aaData' => $rows
		];

		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	/**
	 * Check if given member is editable for current user.
	 *
	 * @param string $member
	 *
	 * @return bool
	 */
	private function isMemberEditable(string $member): bool
	{
		$userModel = \App\User::getCurrentUserModel();
		[$type, $id] = explode(':', $member);
		if (!$userModel->isAdmin() && null === $this->subordinates) {
			$this->subordinates = \App\User::getPrivilegesFile($userModel->getId())['subordinate_roles_users'];
		}

		$editable = $userModel->isAdmin() || $id == $userModel->getId();
		if (!$editable) {
			switch ($type) {
				case \App\PrivilegeUtil::MEMBER_TYPE_USERS:
					foreach ($this->subordinates as $users) {
						if ($editable = isset($users[$id]) || $id === $userModel->getId()) {
							break;
						}
					}
					break;
				case \App\PrivilegeUtil::MEMBER_TYPE_ROLES:
				case \App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES:
					$editable = isset($this->subordinates[$id]);
					break;
				default:
					break;
			}
		}

		return $editable;
	}

	/**
	 * Remove member from group.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function removeMember(App\Request $request)
	{
		$groupId = $request->getInteger('groupID');
		$member = $request->getByType('member', \App\Purifier::TEXT);
		if (!$this->isMemberEditable($member)) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
		}

		$recordModel = \Settings_Groups_Record_Model::getInstance($groupId);
		$memberModel = $recordModel->getFieldInstanceByName('members');
		$members = $memberModel->getEditViewDisplayValue($recordModel->get('members') ?? '');
		if (\count($members) > 1 && false !== ($key = array_search($member, $members))) {
			unset($members[$key]);
			$recordModel->set('members', $memberModel->getDBValue($members));
			$recordModel->save();
		}

		$response = new \Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	/**
	 * Add members to group.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function addMembers(App\Request $request)
	{
		$groupId = $request->getInteger('groupID');
		$recordModel = \Settings_Groups_Record_Model::getInstance($groupId);
		$fieldModel = $recordModel->getFieldInstanceByName('members');
		$newMembers = $request->getByType($fieldModel->getName(), $fieldModel->get('purifyType'));
		$fieldModel->getUITypeModel()->validate($newMembers, true);
		foreach ($newMembers as $member) {
			if (!$this->isMemberEditable($member)) {
				throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 406);
			}
		}

		$currentMembers = $fieldModel->getEditViewDisplayValue($recordModel->get('members') ?? '');
		$members = array_unique(array_merge($currentMembers, $newMembers));
		$recordModel->set('members', $fieldModel->getDBValue($members));

		$result = ['success' => true];
		if ($errorLabel = $recordModel->validate()) {
			$result = ['success' => false, 'message' => \App\Language::translate($errorLabel, 'Settings:Groups')];
		} elseif ($recordModel->getPreviousValue()) {
			$recordModel->save();
		}

		$response = new \Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
