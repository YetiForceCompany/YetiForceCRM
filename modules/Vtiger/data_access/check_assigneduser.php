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
		$allowedUsers = $config['field'];
		$assignedUser = $record_form['assigned_user_id'];
		if (!is_array($allowedUsers))
			$allowedUsers = [$allowedUsers];
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
			return [
				'save_record' => false,
				'type' => 0,
				'info' => [
					'text' => \App\Language::translate($config['info'], 'DataAccess'),
					'type' => 'error'
				]
			];
		else
			return ['save_record' => true];
	}

	public function getConfig($id, $module, $baseModule)
	{
		$users = \App\Fields\Owner::getInstance()->getAccessibleUsers();
		$groups = \App\Fields\Owner::getInstance()->getAccessibleGroups();

		return ['users' => $users, 'groups' => $groups];
	}
}
