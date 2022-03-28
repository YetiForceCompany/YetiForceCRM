<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Import_Queue_Action extends \App\Controller\Action
{
	public static $IMPORT_STATUS_NONE = 0;
	public static $IMPORT_STATUS_SCHEDULED = 1;
	public static $IMPORT_STATUS_RUNNING = 2;
	public static $IMPORT_STATUS_HALTED = 3;
	public static $IMPORT_STATUS_COMPLETED = 4;

	public function __construct()
	{
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
	}

	/**
	 * Adds status to the database.
	 *
	 * @param \App\Request $request
	 * @param \App\User    $user
	 */
	public static function add(App\Request $request, App\User $user)
	{
		if ($request->get('is_scheduled')) {
			$temp_status = self::$IMPORT_STATUS_SCHEDULED;
		} else {
			$temp_status = self::$IMPORT_STATUS_NONE;
		}
		$defaultValues = [];
		if ($request->get('default_values')) {
			$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
			foreach ($request->get('default_values') as $fieldName => $value) {
				if ($fieldModel = $moduleModel->getFieldByName($fieldName)) {
					if ('reference' !== $fieldModel->getFieldDataType()) {
						$uiTypeModel = $fieldModel->getUITypeModel();
						$uiTypeModel->validate($value, true);
						$value = $uiTypeModel->getDBValue($value);
					}
					$defaultValues[$fieldName] = $value;
				}
			}
		}
		\App\Db::getInstance()->createCommand()->insert('vtiger_import_queue', [
			'userid' => $user->getId(),
			'tabid' => \App\Module::getModuleId($request->getModule()),
			'field_mapping' => \App\Json::encode($request->get('field_mapping')),
			'default_values' => \App\Json::encode($defaultValues),
			'merge_type' => $request->get('merge_type'),
			'merge_fields' => \App\Json::encode($request->get('merge_fields')),
			'temp_status' => $temp_status,
		])->execute();
	}

	public static function remove($importId)
	{
		if (vtlib\Utils::checkTable('vtiger_import_queue')) {
			App\Db::getInstance()->createCommand()->delete('vtiger_import_queue', ['importid' => $importId])->execute();
		}
	}

	/**
	 * Remove import for user.
	 *
	 * @param \App\User $user
	 */
	public static function removeForUser(App\User $user)
	{
		if (vtlib\Utils::checkTable('vtiger_import_queue')) {
			App\Db::getInstance()->createCommand()->delete('vtiger_import_queue', ['userid' => $user->getId()])->execute();
		}
	}

	/**
	 * Function to get current import.
	 *
	 * @param \App\User $user
	 *
	 * @return array
	 */
	public static function getUserCurrentImportInfo(App\User $user)
	{
		if (vtlib\Utils::checkTable('vtiger_import_queue')) {
			$rowData = (new App\Db\Query())->from('vtiger_import_queue')->where(['userid' => $user->getId()])->one();
			if ($rowData) {
				return static::getImportInfoFromResult($rowData);
			}
		}
		return null;
	}

	/**
	 * Import info.
	 *
	 * @param string    $module
	 * @param \App\User $user
	 *
	 * @return array|null
	 */
	public static function getImportInfo($module, App\User $user)
	{
		$rowData = (new \App\Db\Query())->from('vtiger_import_queue')->where(['tabid' => \App\Module::getModuleId($module), 'userid' => $user->getId()])->one();
		if ($rowData) {
			return self::getImportInfoFromResult($rowData);
		}
		return null;
	}

	public static function getImportInfoById($importId)
	{
		if (vtlib\Utils::checkTable('vtiger_import_queue')) {
			$rowData = (new App\Db\Query())->from('vtiger_import_queue')->where(['importid' => $importId])->one();
			if ($rowData) {
				return self::getImportInfoFromResult($rowData);
			}
		}
		return null;
	}

	public static function getAll($tempStatus = false)
	{
		$query = (new App\Db\Query())->from('vtiger_import_queue');
		if (false !== $tempStatus) {
			$query->where(['temp_status' => $tempStatus]);
		}
		$dataReader = $query->createCommand()->query();
		$scheduledImports = [];
		while ($row = $dataReader->read()) {
			$scheduledImports[$row['importid']] = self::getImportInfoFromResult($row);
		}
		$dataReader->close();

		return $scheduledImports;
	}

	/**
	 * Import info.
	 *
	 * @param array $rowData
	 *
	 * @return array
	 */
	public static function getImportInfoFromResult($rowData)
	{
		return [
			'id' => $rowData['importid'],
			'module' => \App\Module::getModuleName($rowData['tabid']),
			'field_mapping' => \App\Json::decode($rowData['field_mapping']),
			'default_values' => \App\Json::decode($rowData['default_values']),
			'merge_type' => $rowData['merge_type'],
			'merge_fields' => \App\Json::decode($rowData['merge_fields']),
			'user_id' => $rowData['userid'],
			'temp_status' => $rowData['temp_status'],
		];
	}

	public static function updateStatus($importId, $tempStatus)
	{
		App\Db::getInstance()->createCommand()->update('vtiger_import_queue', ['temp_status' => $tempStatus], ['importid' => $importId])->execute();
	}
}
