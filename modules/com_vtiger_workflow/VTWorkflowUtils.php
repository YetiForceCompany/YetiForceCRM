<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

//A collection of util functions for the workflow module

class VTWorkflowUtils
{

	static $userStack;
	static $loggedInUser;

	public function __construct()
	{
		$current_user = vglobal('current_user');
		if (empty(self::$userStack)) {
			self::$userStack = array();
		}
	}

	/**
	 * Check whether the given identifier is valid.
	 */
	public function validIdentifier($identifier)
	{
		if (is_string($identifier)) {
			return preg_match("/^[a-zA-Z][a-zA-Z_0-9]+$/", $identifier);
		} else {
			return false;
		}
	}

	/**
	 * Push the admin user on to the user stack
	 * and make it the $current_user
	 *
	 */
	public function adminUser()
	{
		$user = Users::getActiveAdminUser();
		$current_user = vglobal('current_user');
		if (empty(self::$userStack) || count(self::$userStack) == 0) {
			self::$loggedInUser = $current_user;
		}
		array_push(self::$userStack, $current_user);
		$current_user = $user;
		return $user;
	}

	/**
	 * Push the logged in user on the user stack
	 * and make it the $current_user
	 */
	public function loggedInUser()
	{
		$user = self::$loggedInUser;
		$current_user = vglobal('current_user');
		array_push(self::$userStack, $current_user);
		$current_user = $user;
		return $user;
	}

	/**
	 * Revert to the previous use on the user stack
	 */
	public function revertUser()
	{
		$current_user = vglobal('current_user');
		if (count(self::$userStack) != 0) {
			$current_user = array_pop(self::$userStack);
		} else {
			$current_user = null;
		}
		return $current_user;
	}

	/**
	 * Get the current user
	 */
	public function currentUser()
	{
		return $current_user;
	}

	/**
	 * The the webservice entity type of an EntityData object
	 */
	public function toWSModuleName($entityData)
	{
		$moduleName = $entityData->getModuleName();
		if ($moduleName == 'Activity') {
			$arr = array('Task' => 'Calendar');
			$type = \vtlib\Functions::getActivityType($entityData->getId());
			$moduleName = $arr[$type];
			if ($moduleName === null) {
				$moduleName = 'Events';
			}
		}
		return $moduleName;
	}

	/**
	 * Insert redirection script
	 */
	public function redirectTo($to, $message)
	{

		?>
		<script type="text/javascript" charset="utf-8">
			window.location = "<?php echo $to ?>";
		</script>
		<a href="<?php echo $to ?>"><?php echo $message ?></a>
		<?php
	}

	/**
	 * Check if the current user is admin
	 */
	public function checkAdminAccess()
	{
		$current_user = vglobal('current_user');
		return strtolower($current_user->is_admin) === 'on';
	}
	/* function to check if the module has workflow
	 * @params :: $modulename - name of the module
	 */

	public static function checkModuleWorkflow($modulename)
	{
		return (new \App\Db\Query())->from('vtiger_tab')
				->where(['NOT IN', 'name', ['Calendar', 'Faq', 'Events', 'Users']])
				->andWhere(['isentitytype' => 1, 'presence' => 0, 'tabid' => \App\Module::getModuleId($modulename)])
				->exists();
	}

	public function vtGetModules($adb)
	{
		$modules_not_supported = ['PBXManager'];
		$sql = sprintf('select distinct vtiger_field.tabid, name
			from vtiger_field
			inner join vtiger_tab
				on vtiger_field.tabid=vtiger_tab.tabid
			where vtiger_tab.name not in(%s) and vtiger_tab.isentitytype=1 and vtiger_tab.presence in (0,2) ', generateQuestionMarks($modules_not_supported));
		$it = new SqlResultIterator($adb, $adb->pquery($sql, [$modules_not_supported]));
		$modules = array();
		foreach ($it as $row) {
			$modules[] = $row->name;
		}
		return $modules;
	}
}
