<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

namespace vtlib;

/**
 * Provides API to work with vtiger CRM Profile.
 */
class Profile
{
	public $id;
	public $name;
	public $desc;

	public function save()
	{
		if (!$this->id) {
			$this->create();
		} else {
			$this->update();
		}
	}

	private function create()
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_profile', [
			'profilename' => $this->name,
			'description' => $this->desc,
		])->execute();
		$this->id = $db->getLastInsertID('vtiger_profile_profileid_seq');
		$dataReader = (new \App\Db\Query())->select(['tabid', 'fieldid'])->from('vtiger_field')
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$db->createCommand()->insert('vtiger_profile2field', [
				'profileid' => $this->id,
				'tabid' => $row['tabid'],
				'fieldid' => $row['fieldid'],
				'visible' => 0,
				'readonly' => 0,
			])->execute();
		}
		$dataReader = (new \App\Db\Query())->select(['tabid'])->from('vtiger_tab')
			->createCommand()->query();
		$insertedData = [];
		while ($row = $dataReader->read()) {
			$insertedData[] = [$this->id, $row['tabid'], 0];
		}
		$db->createCommand()->batchInsert('vtiger_profile2tab', ['profileid', 'tabid', 'permissions'], $insertedData)->execute();
		$dataReader = (new \App\Db\Query())->select(['tabid', 'actionid'])->from(['vtiger_actionmapping', 'vtiger_tab'])
			->where(['actionname' => ['Save', 'EditView', 'Delete', 'index', 'DetailView'], 'isentitytype' => 1])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$db->createCommand()->insert('vtiger_profile2standardpermissions', [
				'profileid' => $this->id,
				'tabid' => $row['tabid'],
				'operation' => $row['actionid'],
				'permissions' => 0,
			])->execute();
		}
		\App\Log::trace('Initializing profile permissions ... DONE', __METHOD__);
	}

	private function update()
	{
		throw new \App\Exceptions\AppException('Not implemented');
	}

	/**
	 * Initialize profile setup for Field.
	 *
	 * @param FieldBasic $fieldInstance
	 */
	public static function initForField(FieldBasic $fieldInstance)
	{
		$profileids = self::getAllIds();
		$insertedValues = [];
		foreach ($profileids as &$profileid) {
			$insertedValues[] = [$profileid, $fieldInstance->getModuleId(), $fieldInstance->id, 0, 0];
		}
		\App\Db::getInstance()->createCommand()->batchInsert('vtiger_profile2field', ['profileid', 'tabid', 'fieldid', 'visible', 'readonly'], $insertedValues)->execute();
	}

	/**
	 * Delete profile information related with field.
	 *
	 * @param FieldBasic $fieldInstance
	 */
	public static function deleteForField(FieldBasic $fieldInstance)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_profile2field', ['fieldid' => $fieldInstance->id])->execute();
	}

	/**
	 * Get all the existing profile ids.
	 */
	public static function getAllIds()
	{
		if (\App\Cache::has('AllProfileIds', '')) {
			return \App\Cache::get('AllProfileIds', '');
		}
		$profiles = (new \App\Db\Query())->select(['profileid'])->from('vtiger_profile')->column();
		\App\Cache::save('AllProfileIds', '', $profiles);

		return $profiles;
	}

	/**
	 * Initialize profile setup for the module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public static function initForModule(ModuleBasic $moduleInstance)
	{
		$db = \App\Db::getInstance();
		$actionids = (new \App\Db\Query())->select(['actionid'])->from('vtiger_actionmapping')
			->where(['actionname' => ['Save', 'EditView', 'Delete', 'index', 'DetailView', 'CreateView']])
			->column();
		$profileids = self::getAllIds();
		foreach ($profileids as &$profileid) {
			$db->createCommand()->insert('vtiger_profile2tab', [
				'profileid' => $profileid,
				'tabid' => $moduleInstance->id,
				'permissions' => 0,
			])->execute();
			if ($moduleInstance->isentitytype) {
				foreach ($actionids as &$actionid) {
					$db->createCommand()->insert('vtiger_profile2standardpermissions', [
						'profileid' => $profileid,
						'tabid' => $moduleInstance->id,
						'operation' => $actionid,
						'permissions' => 0,
					])->execute();
				}
			}
		}
		\App\Log::trace('Initializing module permissions ... DONE', __METHOD__);
	}

	/**
	 * Delete profile setup of the module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public static function deleteForModule(ModuleBasic $moduleInstance)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->delete('vtiger_def_org_share', ['tabid' => $moduleInstance->id])->execute();
		$db->createCommand()->delete('vtiger_profile2field', ['tabid' => $moduleInstance->id])->execute();
		$db->createCommand()->delete('vtiger_profile2standardpermissions', ['tabid' => $moduleInstance->id])->execute();
		$db->createCommand()->delete('vtiger_profile2tab', ['tabid' => $moduleInstance->id])->execute();
	}
}
