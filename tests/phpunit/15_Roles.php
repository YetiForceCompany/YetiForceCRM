<?php
/**
 * Groups test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers Groups::<public>
 */
class Roles extends TestCase
{

	/**
	 * Role id
	 */
	private static $id;

	/**
	 * Testing role creation
	 */
	public function testAddRole()
	{
		$recordModel = new Settings_Roles_Record_Model();
		$parentRole = Settings_Roles_Record_Model::getInstanceById('H2');
		$this->assertNotNull($parentRole);

		$recordModel->set('change_owner', '1');
		$recordModel->set('searchunpriv', ['Contacts']);
		$recordModel->set('listrelatedrecord', '0');
		$recordModel->set('editrelatedrecord', '1');
		$recordModel->set('permissionsrelatedfield', ['0']);
		$recordModel->set('globalsearchadv', '1');
		$recordModel->set('assignedmultiowner', '1');
		$recordModel->set('clendarallorecords', '1');
		$recordModel->set('auto_assign', '1');
		$recordModel->set('rolename', 'Test');
		$recordModel->set('profileIds', ['1']);
		$recordModel->set('allowassignedrecordsto', '1');
		$recordModel->set('clendarallorecords', '1');
		$recordModel->set('previewrelatedrecord', '0');
		$recordModel->setParent($parentRole);
		$recordModel->save();
		self::$id = $recordModel->getId();
		$this->assertNotNull(self::$id);

		$row = (new \App\Db\Query())->from('vtiger_role')->where(['roleid' => static::$id])->one();

		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['rolename'], 'Test');
		$this->assertEquals($row['changeowner'], '1');
		$this->assertEquals($row['searchunpriv'], 'Contacts');
		$this->assertEquals($row['parentrole'], 'H1::H2::' . static::$id);
		$this->assertEquals($row['allowassignedrecordsto'], '1');
		$this->assertEquals($row['clendarallorecords'], '1');
		$this->assertEquals($row['listrelatedrecord'], '0');
		$this->assertEquals($row['previewrelatedrecord'], '0');
		$this->assertEquals($row['editrelatedrecord'], '1');
		$this->assertEquals($row['permissionsrelatedfield'], '0');
		$this->assertEquals($row['globalsearchadv'], '1');
		$this->assertEquals($row['assignedmultiowner'], '1');
		$this->assertEquals($row['auto_assign'], '1');
	}

	/**
	 * Testing role deletion
	 */
	public function testDeleteRole()
	{
		$recordModel = Settings_Roles_Record_Model::getInstanceById(self::$id);
		$transferToRole = Settings_Roles_Record_Model::getInstanceById('H6');
		$this->assertNotNull($recordModel);
		$this->assertNotNull($transferToRole);
		$recordModel->delete($transferToRole);

		$row = (new \App\Db\Query())->from('vtiger_role')->where(['roleid' => static::$id])->one();
		$this->assertFalse($row);
	}
}
