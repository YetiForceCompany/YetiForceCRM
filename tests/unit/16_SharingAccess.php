<?php

/**
 * SharingAccess test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers SharingAccess::<public>
 */
class SharingAccess extends TestCase
{

	/**
	 * Change of permissions
	 */
	private function changePermissions($modulePermissions)
	{
		$modulePermissions[4] = $modulePermissions[6];

		$postValues = [];
		$prevValues = [];
		foreach ($modulePermissions as $tabId => $permission) {
			$moduleModel = Settings_SharingAccess_Module_Model::getInstance($tabId);
			$permissionOld = $moduleModel->get('permission');
			$moduleModel->set('permission', $permission);
			if ($permissionOld != $permission) {
				$prevValues[$tabId] = $permissionOld;
				$postValues[$tabId] = $moduleModel->get('permission');
				if ($permissionOld == 3 || $moduleModel->get('permission') == 3) {
					\App\Privilege::setUpdater(\App\Module::getModuleName($tabId));
				}
			}
			try {
				$moduleModel->save();
			} catch (\App\Exceptions\AppException $e) {
				$this->assertTrue(true, $e->getMessage());
			}
		}
		Settings_Vtiger_Tracker_Model::addDetail($prevValues, $postValues);
		Settings_SharingAccess_Module_Model::recalculateSharingRules();
	}

	/**
	 * Testing permission changes
	 */
	public function testChangePermissions()
	{
		$row = (new \App\Db\Query())->from('vtiger_def_org_share')->where(['tabid' => 6])->one();
		$this->assertNotFalse($row, 'No record id: 6');

		$oldPermission = $row['permission'];
		$newPermission = $oldPermission == 2 ? 1 : 2;

		$modulePermissions = [6 => $newPermission, 4 => $newPermission];
		$this->changePermissions($modulePermissions);

		foreach ($modulePermissions as $tabId => $permission) {
			$this->assertEquals((new \App\Db\Query())->select('permission')->from('vtiger_def_org_share')->where(['tabid' => $tabId])->scalar(), $permission);
		}

		$modulePermissions = [6 => $oldPermission, 4 => $oldPermission];
		$this->changePermissions($modulePermissions);
	}
}
