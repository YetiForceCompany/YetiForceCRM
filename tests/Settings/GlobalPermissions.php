<?php
/**
 * GlobalPermissions test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Settings;

class GlobalPermissions extends \Tests\Base
{
	/**
	 * Testing permission changes.
	 */
	public function testChangeGlobalPermission()
	{
		$profileID = 1;
		$checked = 0;
		$globalactionid = 1;
		\Settings_GlobalPermission_Record_Model::save($profileID, $globalactionid, $checked);

		$row = (new \App\Db\Query())->from('vtiger_profile2globalpermissions')->where(['profileid' => $profileID, 'globalactionid' => $globalactionid])->all();

		$this->assertCount(1, $row);
		$this->assertSame($row[0]['globalactionpermission'], $checked);
	}

	/**
	 * Testing permission changes back.
	 */
	public function testChangeBackGlobalPermission()
	{
		$profileID = 1;
		$checked = 1;
		$globalactionid = 1;
		\Settings_GlobalPermission_Record_Model::save($profileID, $globalactionid, $checked);

		$row = (new \App\Db\Query())->from('vtiger_profile2globalpermissions')->where(['profileid' => $profileID, 'globalactionid' => $globalactionid])->all();

		$this->assertCount(1, $row);
		$this->assertSame($row[0]['globalactionpermission'], $checked);
	}
}
