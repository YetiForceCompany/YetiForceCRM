<?php
/**
 * SharingAccess test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Settings;

class SharingAccess extends \Tests\Base
{
	/**
	 * Share id.
	 */
	private static $shareId;

	/**
	 * Relation type.
	 */
	private static $relationType;

	/**
	 * Testing add shared access policy.
	 */
	public function testAddSharedAccessPolicy()
	{
		$sourceId = 'Groups:2';
		$targetId = 'Groups:2';
		$permission = 0;
		$ruleId = 0;
		$forModule = 'Accounts';
		$ruleModel = $this->saveRule($forModule, $ruleId, $permission, $sourceId, $targetId);

		static::$shareId = $ruleModel->get('shareid');
		static::$relationType = $ruleModel->get('relationtype');
		$relationTypeComponents = explode('::', static::$relationType);
		$sourceType = $relationTypeComponents[0];
		$targetType = $relationTypeComponents[1];

		$row = (new \App\Db\Query())->from('vtiger_datashare_module_rel')->where(['shareid' => static::$shareId])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$shareId);
		$relationType = implode('::', [$sourceType, $targetType]);

		$this->assertSame($ruleModel->getModule()->getId(), $row['tabid']);
		$this->assertSame($relationType, $row['relationtype']);

		$tableColumnInfo = \Settings_SharingAccess_Rule_Model::$dataShareTableColArr[$sourceType][$targetType];
		$tableName = $tableColumnInfo['table'];
		$sourceColumnName = $tableColumnInfo['source_id'];
		$targetColumnName = $tableColumnInfo['target_id'];
		$row2 = (new \App\Db\Query())->from($tableName)->where(['shareid' => static::$shareId])->one();
		$this->assertNotFalse($row2, 'No record id: ' . static::$shareId);

		$sourceIdComponents = \Settings_SharingAccess_RuleMember_Model::getIdComponentsFromQualifiedId($sourceId);
		$targetIdComponents = \Settings_SharingAccess_RuleMember_Model::getIdComponentsFromQualifiedId($targetId);

		$this->assertSame($permission, $row2['permission']);
		$this->assertSame((string) $sourceIdComponents[1], (string) $row2[$sourceColumnName]);
		$this->assertSame((string) $targetIdComponents[1], (string) $row2[$targetColumnName]);
	}

	/**
	 * Save permissions.
	 *
	 * @param string $forModule
	 * @param int    $ruleId
	 * @param int    $permission
	 * @param string $sourceId
	 * @param string $targetId
	 *
	 * @return \Settings_SharingAccess_Rule_Model
	 */
	private function saveRule($forModule, $ruleId, $permission, $sourceId, $targetId)
	{
		\Settings_Vtiger_Tracker_Model::lockTracking(false);
		\Settings_Vtiger_Tracker_Model::addBasic('save');

		$moduleModel = \Settings_SharingAccess_Module_Model::getInstance($forModule);
		if (empty($ruleId)) {
			$ruleModel = new \Settings_SharingAccess_Rule_Model();
			$ruleModel->setModuleFromInstance($moduleModel);
		} else {
			$ruleModel = \Settings_SharingAccess_Rule_Model::getInstance($moduleModel, $ruleId);
		}

		$prevValues['permission'] = $ruleModel->getPermission();
		$newValues['permission'] = $permission;

		\Settings_Vtiger_Tracker_Model::addDetail($prevValues, $newValues);

		$ruleModel->set('source_id', $sourceId);
		$ruleModel->set('target_id', $targetId);
		$ruleModel->set('permission', $permission);
		$ruleModel->save();

		return $ruleModel;
	}

	/**
	 * Testing edit shared access policy.
	 */
	public function testEditSharedAccessPolicy()
	{
		$sourceId = 'Groups:2';
		$targetId = 'Roles:H6';
		$permission = 1;
		$forModule = 'Accounts';
		$ruleModel = $this->saveRule($forModule, static::$shareId, $permission, $sourceId, $targetId);

		$relationTypeComponents = explode('::', static::$relationType);
		$sourceType = $relationTypeComponents[0];
		$targetType = $relationTypeComponents[1];
		$tableColumnInfo = \Settings_SharingAccess_Rule_Model::$dataShareTableColArr[$sourceType][$targetType];
		$tableName = $tableColumnInfo['table'];

		$this->assertFalse((new \App\Db\Query())->from($tableName)->where(['shareid' => static::$shareId])->exists(), 'Record id ' . static::$shareId . ' from table ' . $tableName . ' should not exist');

		$relationTypeComponents = explode('::', $ruleModel->get('relationtype'));
		$sourceType = $relationTypeComponents[0];
		$targetType = $relationTypeComponents[1];
		$tableColumnInfo = \Settings_SharingAccess_Rule_Model::$dataShareTableColArr[$sourceType][$targetType];
		$tableName = $tableColumnInfo['table'];
		$sourceColumnName = $tableColumnInfo['source_id'];
		$targetColumnName = $tableColumnInfo['target_id'];

		$row2 = (new \App\Db\Query())->from($tableName)->where(['shareid' => static::$shareId])->one();
		$this->assertNotFalse($row2, 'No record id: ' . static::$shareId);

		$sourceIdComponents = \Settings_SharingAccess_RuleMember_Model::getIdComponentsFromQualifiedId($sourceId);
		$targetIdComponents = \Settings_SharingAccess_RuleMember_Model::getIdComponentsFromQualifiedId($targetId);

		$this->assertSame($permission, $row2['permission']);
		$this->assertSame((string) $sourceIdComponents[1], (string) $row2[$sourceColumnName]);
		$this->assertSame((string) $targetIdComponents[1], (string) $row2[$targetColumnName]);
	}

	/**
	 * Testing delete shared access policy.
	 */
	public function testDeleteSharedAccessPolicy()
	{
		\Settings_Vtiger_Tracker_Model::lockTracking(false);
		\Settings_Vtiger_Tracker_Model::addBasic('delete');
		$forModule = 'Accounts';

		$moduleModel = \Settings_SharingAccess_Module_Model::getInstance($forModule);
		$ruleModel = \Settings_SharingAccess_Rule_Model::getInstance($moduleModel, static::$shareId);
		$relationType = $ruleModel->get('relationtype');
		$ruleModel->delete();

		$this->assertFalse((new \App\Db\Query())->from('vtiger_datashare_module_rel')->where(['shareid' => static::$shareId])->exists(), 'Record id ' . static::$shareId . ' should not exist');

		$relationTypeComponents = explode('::', $relationType);
		$tableColumnInfo = \Settings_SharingAccess_Rule_Model::$dataShareTableColArr[$relationTypeComponents[0]][$relationTypeComponents[1]];
		$this->assertFalse((new \App\Db\Query())->from($tableColumnInfo['table'])->where(['shareid' => static::$shareId])->exists(), 'Record id ' . static::$shareId . ' from table ' . $tableColumnInfo['table'] . ' should not exist');
	}

	/**
	 * Testing permission changes.
	 */
	public function testChangePermissions()
	{
		$row = (new \App\Db\Query())->from('vtiger_def_org_share')->where(['tabid' => 6])->one();
		$this->assertNotFalse($row, 'No record id: 6');

		$oldPermission = $row['permission'];
		$newPermission = $oldPermission === 2 ? 1 : 2;

		$modulePermissions = [6 => $newPermission, 4 => $newPermission];
		$this->changePermissions($modulePermissions);

		foreach ($modulePermissions as $tabId => $permission) {
			$this->assertSame((new \App\Db\Query())->select('permission')->from('vtiger_def_org_share')->where(['tabid' => $tabId])->scalar(), $permission);
		}

		$modulePermissions = [6 => $oldPermission, 4 => $oldPermission];
		$this->changePermissions($modulePermissions);
	}

	/**
	 * Change of permissions.
	 *
	 * @param array $modulePermissions
	 */
	private function changePermissions($modulePermissions)
	{
		$modulePermissions[4] = $modulePermissions[6];

		$postValues = [];
		$prevValues = [];
		foreach ($modulePermissions as $tabId => $permission) {
			$permission = (int) $permission;
			$moduleModel = \Settings_SharingAccess_Module_Model::getInstance($tabId);
			$permissionOld = (int) $moduleModel->get('permission');
			$moduleModel->set('permission', $permission);
			if ($permissionOld !== $permission) {
				$prevValues[$tabId] = $permissionOld;
				$postValues[$tabId] = (int) $moduleModel->get('permission');
			}
			$moduleModel->save();
		}
		\Settings_Vtiger_Tracker_Model::addDetail($prevValues, $postValues);
		\Settings_SharingAccess_Module_Model::recalculateSharingRules();
	}
}
