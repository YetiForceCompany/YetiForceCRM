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
 * Class Import_Lock_Action
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
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
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
	 * @param object $user
	 */
	public static function lock($importId, $module, $user)
	{
		if (!vtlib\Utils::CheckTable('vtiger_import_locks')) {
			vtlib\Utils::CreateTable(
				'vtiger_import_locks', "(vtiger_import_lock_id INT NOT NULL PRIMARY KEY,
				userid INT NOT NULL,
				tabid INT NOT NULL,
				importid INT NOT NULL,
				locked_since DATETIME)", true);
		}
		\App\Db::getInstance()->createCommand()
			->insert('vtiger_import_locks', [
				'vtiger_import_lock_id' => \App\Db::getInstance()->getUniqueID('vtiger_import_locks'),
				'userid' => $user->id,
				'tabid' => \App\Module::getModuleId($module),
				'importid' => $importId,
				'locked_since' => date('Y-m-d H:i:s')
			])->execute();
	}

	/**
	 * Unlock
	 * @param object $user
	 * @param string $module
	 */
	public static function unLock($user, $module = false)
	{
		if (vtlib\Utils::CheckTable('vtiger_import_locks')) {
			$where = ['userid' => method_exists($user, 'get') ? $user->get('id') : $user->id];
			if ($module !== false) {
				$where['tabid'] = \App\Module::getModuleId($module);
			}
			\App\Db::getInstance()->createCommand()->delete('vtiger_import_locks', $where)->execute();
		}
	}

	/**
	 * Is locked for module
	 * @param string $module
	 * @return null
	 */
	public static function isLockedForModule($module)
	{
		if (vtlib\Utils::CheckTable('vtiger_import_locks')) {
			$lockResult = (new \App\Db\Query())
					->from('vtiger_import_locks')
					->where(['tabid' => \App\Module::getModuleId($module)])->one();
			if ($lockResult) {
				return $lockResult;
			}
		}
		return null;
	}
}
