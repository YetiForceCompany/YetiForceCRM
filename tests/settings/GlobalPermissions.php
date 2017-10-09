<?php
/**
 * GlobalPermissions test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class GlobalPermissions extends TestCase
{

	/**
	 * Testing permission changes
	 */
	public function testChangeGlobalPermission()
	{
		$profileID = 1;
		$checked = 0;
		$globalactionid = 1;
		Settings_GlobalPermission_Record_Model::save($profileID, $globalactionid, $checked);

		$row = (new \App\Db\Query())->from('vtiger_profile2globalpermissions')->where(['profileid' => $profileID, 'globalactionid' => $globalactionid])->all();

		$this->assertCount(1, $row);
		$this->assertEquals($row[0]['globalactionpermission'], $checked);
	}

	/**
	 * Testing permission changes back
	 */
	public function testChangeBackGlobalPermission()
	{
		$profileID = 1;
		$checked = 1;
		$globalactionid = 1;
		Settings_GlobalPermission_Record_Model::save($profileID, $globalactionid, $checked);

		$row = (new \App\Db\Query())->from('vtiger_profile2globalpermissions')->where(['profileid' => $profileID, 'globalactionid' => $globalactionid])->all();

		$this->assertCount(1, $row);
		$this->assertEquals($row[0]['globalactionpermission'], $checked);
	}
}
