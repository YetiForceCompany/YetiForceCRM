<?php
/*
  Return Description
  ------------------------
  Info type: error, info, success
  Info title: optional
  Info text: mandatory
  Type: 0 - notify
  Type: 1 - show quick create mondal
 */

Class DataAccess_check_assigneduser
{

	public $config = true;

	public function process($ModuleName, $ID, $record_form, $config)
	{
		$db = PearDatabase::getInstance();
		$allowedUsers = $config['field'];
		$assignedUser = $record_form['assigned_user_id'];
		if (!is_array($allowedUsers))
			$allowedUsers = array($allowedUsers);
		if (in_array("currentUser", $allowedUsers)) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$allowedUsers[] = $currentUser->get('id');
			foreach ($allowedUsers as $key => $value) {
				if ($value == "currentUser") {
					unset($allowedUsers[$key]);
				}
			}
		}
		if (!in_array($assignedUser, $allowedUsers))
			return Array(
				'save_record' => false,
				'type' => 0,
				'info' => Array(
					'text' => vtranslate($config['info'], 'DataAccess'),
					'type' => 'error'
				)
			);
		else
			return Array('save_record' => true);
	}

	public function getConfig($id, $module, $baseModule)
	{
		$users = \App\Fields\Owner::getInstance()->getAccessibleUsers();
		$groups = \App\Fields\Owner::getInstance()->getAccessibleGroups();

		return Array('users' => $users, 'groups' => $groups);
	}
}
