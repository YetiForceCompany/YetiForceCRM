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
class Import_Lock_Action extends \App\Controller\Action
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		
	}

	/**
	 * {@inheritDoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritDoc}
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

	/**
	 * Unlock
	 * @param Users_Record_Model $user
	 * @param string $module
	 */
	public static function unLock($user, $module = false)
	{
		$db = \App\Db::getInstance();
		$where = ['userid' => method_exists($user, 'get') ? $user->get('id') : $user->id];
		if ($module) {
			$where['tabid'] = \App\Module::getModuleId($module);
		}
		$db->createCommand()->delete('vtiger_import_locks', $where)->execute();
	}

	/**
	 * Is locked for module
	 * @param string $module
	 * @return array|bool
	 */
	public static function isLockedForModule($module)
	{
		return (new \App\Db\Query())
				->from('vtiger_import_locks')
				->where(['tabid' => \App\Module::getModuleId($module)])->one();
	}
}
