<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Import lock action
 */
class Import_Lock_Action extends Vtiger_Action_Controller
{

	/**
	 * Constructor
	 */
	public function __construct()
	{

	}

	/**
	 * Check permission
	 * @param \App\Request $request
	 * @throws \Exception\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process
	 * @param \App\Request $request
	 * @return boolean
	 */
	public function process(\App\Request $request)
	{
		return false;
	}

	/**
	 * Lock
	 * @param int $importId
	 * @param string $module
	 * @param Users_Record_Model $user
	 */
	public static function lock($importId, $module, $user)
	{
		\App\Db::getInstance()->createCommand()
			->insert('vtiger_import_locks', [
				'userid' => $user->id,
				'tabid' => \App\Module::getModuleId($module),
				'importid' => $importId,
				'locked_since' => date('Y-m-d H:i:s')
			])->execute();
	}

	public static function unLock($user, $module = false)
	{
		$adb = PearDatabase::getInstance();
		if (vtlib\Utils::CheckTable('vtiger_import_locks')) {
			$query = 'DELETE FROM vtiger_import_locks WHERE userid=?';
			$params = array(method_exists($user, 'get') ? $user->get('id') : $user->id);
			if ($module !== false) {
				$query .= ' && tabid=?';
				array_push($params, \App\Module::getModuleId($module));
			}
			$adb->pquery($query, $params);
		}
	}

	public static function isLockedForModule($module)
	{
		$adb = PearDatabase::getInstance();

		if (vtlib\Utils::CheckTable('vtiger_import_locks')) {
			$lockResult = $adb->pquery('SELECT * FROM vtiger_import_locks WHERE tabid=?', array(\App\Module::getModuleId($module)));

			if ($lockResult && $adb->num_rows($lockResult) > 0) {
				$lockInfo = $adb->query_result_rowdata($lockResult, 0);
				return $lockInfo;
			}
		}

		return null;
	}
}
