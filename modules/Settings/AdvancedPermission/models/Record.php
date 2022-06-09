<?php

/**
 * Advanced permission record model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_AdvancedPermission_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Function to get the Id.
	 *
	 * @return int Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get the Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Function to get the Edit View Url.
	 *
	 * @param mixed $step
	 *
	 * @return string URL
	 */
	public function getEditViewUrl($step = false)
	{
		$mode = '';
		if (false !== $step) {
			$mode = '&mode=step' . $step;
		}
		return '?module=AdvancedPermission&parent=Settings&view=Edit&record=' . $this->getId() . $mode;
	}

	/**
	 * Function to get the Detail Url.
	 *
	 * @return string URL
	 */
	public function getDetailViewUrl()
	{
		return '?module=AdvancedPermission&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/**
	 * Function to get the instance of advanced permission record model.
	 *
	 * @param int $id
	 *
	 * @return \self instance, if exists
	 */
	public static function getInstance($id)
	{
		$db = \App\Db::getInstance('admin');
		$query = (new \App\Db\Query())->from('a_#__adv_permission')->where(['id' => $id]);
		$row = $query->createCommand($db)->queryOne();
		$instance = false;
		if (false !== $row) {
			$row['conditions'] = \App\Json::decode($row['conditions']);
			$row['members'] = \App\Json::decode($row['members']);
			$instance = new self();
			$instance->setData($row);
		}
		return $instance;
	}

	/**
	 * Function to save.
	 */
	public function save()
	{
		$db = \App\Db::getInstance('admin');
		$recordId = $this->getId();

		$params = [];
		foreach ($this->getData() as $key => $value) {
			if ($this->has($key)) {
				$params[$key] = $value;
			}
		}
		if (isset($params['conditions'])) {
			$params['conditions'] = \App\Json::encode($params['conditions']);
		}
		if (isset($params['members'])) {
			$params['members'] = \App\Json::encode($params['members']);
		}
		if (!$recordId) {
			$db->createCommand()->insert('a_#__adv_permission', $params)->execute();
			$this->set('id', $db->getLastInsertID('a_#__adv_permission_id_seq'));
		} else {
			$db->createCommand()->update('a_#__adv_permission', $params, ['id' => $recordId])->execute();
		}
		\App\PrivilegeAdvanced::reloadCache();
		if ($this->has('conditions')) {
			\App\Privilege::setUpdater(\App\Module::getModuleName($this->get('tabid')));
		}
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getDisplayValue(string $key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'tabid':
				$value = \App\Module::getModuleName($value);
				break;
			case 'status':
				if (isset(Settings_AdvancedPermission_Module_Model::$status[$value])) {
					$value = Settings_AdvancedPermission_Module_Model::$status[$value];
				}
				break;
			case 'action':
				if (isset(Settings_AdvancedPermission_Module_Model::$action[$value])) {
					$value = Settings_AdvancedPermission_Module_Model::$action[$value];
				}
				break;
			case 'priority':
				if (isset(Settings_AdvancedPermission_Module_Model::$priority[$value])) {
					$value = Settings_AdvancedPermission_Module_Model::$priority[$value];
				}
				break;
			case 'members':
				if (!empty($value)) {
					$values = [];
					foreach ($value as $member) {
						[$type, $id] = explode(':', $member);
						switch ($type) {
							case 'Users':
								$name = \App\Fields\Owner::getUserLabel($id);
								break;
							case 'Groups':
								$name = \App\Language::translate(\App\Fields\Owner::getGroupName($id));
								break;
							case 'Roles':
								$roleInfo = \App\PrivilegeUtil::getRoleDetail($id);
								$name = \App\Language::translate($roleInfo['rolename']);
								break;
							case 'RoleAndSubordinates':
								$roleInfo = \App\PrivilegeUtil::getRoleDetail($id);
								$name = \App\Language::translate($roleInfo['rolename']);
								break;
							default:
								break;
						}
						$values[] = \App\Language::translate($type) . ': ' . $name;
					}
					$value = implode(', ', $values);
				}
				break;
			default:
				break;
		}
		return $value;
	}

	/**
	 * Function to delete the current Record Model.
	 *
	 * @return bool
	 */
	public function delete(): bool
	{
		$delete = \App\Db::getInstance('admin')->createCommand()
			->delete('a_#__adv_permission', ['id' => $this->getId()])
			->execute();
		if ($delete) {
			\App\PrivilegeAdvanced::reloadCache();
			if ($this->has('conditions')) {
				\App\Privilege::setUpdater(\App\Module::getModuleName($this->get('tabid')));
			}
		}
		return $delete;
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-primary btn-sm',
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.deleteById('{$this->getId()}')",
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn btn-danger btn-sm',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to retrieve a list of users.
	 *
	 * @return array
	 */
	public function getUserByMember()
	{
		$members = $this->get('members');
		$users = [];
		if (!empty($members)) {
			foreach ($members as &$member) {
				$users = array_merge($users, \App\PrivilegeUtil::getUserByMember($member));
			}
			$users = array_unique($users);
		}
		$users = array_flip($users);
		foreach ($users as $id => &$user) {
			$user = \App\Fields\Owner::getUserLabel($id);
		}
		return $users;
	}
}
