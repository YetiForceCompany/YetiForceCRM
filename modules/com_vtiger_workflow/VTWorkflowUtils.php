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

/**
 * Class vTWorkflowUtils
 */
class VTWorkflowUtils
{

	/**
	 * User stack
	 * @var array
	 */
	public static $userStack;

	/**
	 * Logged in user id
	 * @var int
	 */
	public static $loggedInUser;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		if (empty(self::$userStack)) {
			self::$userStack = [];
		}
	}

	/**
	 * Check whether the given identifier is valid.
	 * @param string $identifier Description
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
	 * Insert redirection script
	 * @param string $to
	 * @param string $message
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

	/** function to check if the module has workflow
	 * @param string $modulename - name of the module
	 */
	public static function checkModuleWorkflow($modulename)
	{
		return (new \App\Db\Query())->from('vtiger_tab')->where(['NOT IN', 'name', ['Calendar', 'Faq', 'Events', 'Users']])->andWhere(['isentitytype' => 1, 'presence' => 0, 'tabid' => \App\Module::getModuleId($modulename)])->exists();
	}

	/**
	 * Get modules
	 * @return array
	 */
	public function vtGetModules()
	{
		$modules_not_supported = ['PBXManager'];
		$query = (new \App\Db\Query())->select(['vtiger_field.tabid', 'name'])->from('vtiger_field')->innerJoin('vtiger_tab', 'vtiger_field.tabid=vtiger_tab.tabid')->where(['vtiger_tab.isentitytype' => 1, 'vtiger_tab.presence' => [0, 2]])->andWhere(['NOT IN', 'vtiger_tab.name', $modules_not_supported])->distinct();
		$modules = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$modules[] = $row['name'];
		}
		return $modules;
	}
}
