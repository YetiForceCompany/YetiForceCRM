<?php
/**
 * Roles test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Settings;

class Roles extends \Tests\Base
{
	/**
	 * Role id.
	 */
	private static $id;

	/**
	 * Testing role creation.
	 */
	public function testAddRole()
	{
		$recordModel = new \Settings_Roles_Record_Model();
		$parentRole = \Settings_Roles_Record_Model::getInstanceById('H2');
		$this->assertNotNull($parentRole);
		$recordModel->set('change_owner', 1);
		$recordModel->set('searchunpriv', ['Contacts']);
		$recordModel->set('listrelatedrecord', 0);
		$recordModel->set('editrelatedrecord', 1);
		$recordModel->set('permissionsrelatedfield', [0]);
		$recordModel->set('globalsearchadv', 1);
		$recordModel->set('assignedmultiowner', 1);
		$recordModel->set('clendarallorecords', 1);
		$recordModel->set('auto_assign', 1);
		$recordModel->set('rolename', 'Test');
		$recordModel->set('profileIds', [1]);
		$recordModel->set('allowassignedrecordsto', 1);
		$recordModel->set('clendarallorecords', 1);
		$recordModel->set('previewrelatedrecord', 0);
		$recordModel->setParent($parentRole);
		$recordModel->save();
		static::$id = $recordModel->getId();
		$this->assertNotNull(static::$id);

		$row = (new \App\Db\Query())->from('vtiger_role')->where(['roleid' => static::$id])->one();
		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertSame($row['rolename'], 'Test');
		$this->assertSame((string) $row['changeowner'], '1');
		$this->assertSame($row['searchunpriv'], 'Contacts');
		$this->assertSame($row['parentrole'], 'H1::H2::' . static::$id);
		$this->assertSame((string) $row['allowassignedrecordsto'], '1');
		$this->assertSame((string) $row['clendarallorecords'], '1');
		$this->assertSame((string) $row['listrelatedrecord'], '0');
		$this->assertSame((string) $row['previewrelatedrecord'], '0');
		$this->assertSame((string) $row['editrelatedrecord'], '1');
		$this->assertSame((string) $row['permissionsrelatedfield'], '0');
		$this->assertSame((string) $row['globalsearchadv'], '1');
		$this->assertSame((string) $row['assignedmultiowner'], '1');
		$this->assertSame((string) $row['auto_assign'], '1');
	}

	/**
	 * Testing move role.
	 */
	public function testMoveRole()
	{
		$parentRole = \Settings_Roles_Record_Model::getInstanceById('H1');
		$recordModel = \Settings_Roles_Record_Model::getInstanceById(static::$id);
		$recordModel->setParent($parentRole);
		$recordModel->moveTo($parentRole);

		$row = (new \App\Db\Query())->from('vtiger_role')->where(['roleid' => static::$id])->one();
		$this->assertSame($row['parentrole'], 'H1::' . static::$id);
	}

	/**
	 * Testing role edition.
	 */
	public function testEditRole()
	{
		$recordModel = \Settings_Roles_Record_Model::getInstanceById(static::$id);
		$this->assertNotNull($recordModel);

		$recordModel->set('change_owner', 0);
		$recordModel->set('searchunpriv', ['Contacts', 'Accounts']);
		$recordModel->set('listrelatedrecord', 1);
		$recordModel->set('editrelatedrecord', 0);
		$recordModel->set('permissionsrelatedfield', [0, 1]);
		$recordModel->set('globalsearchadv', 0);
		$recordModel->set('assignedmultiowner', 4);
		$recordModel->set('clendarallorecords', 2);
		$recordModel->set('auto_assign', 0);
		$recordModel->set('rolename', 'Test edit');
		$recordModel->set('profileIds', ['1', '2']);
		$recordModel->set('allowassignedrecordsto', 4);
		$recordModel->set('clendarallorecords', 2);
		$recordModel->set('previewrelatedrecord', 1);
		$recordModel->save();

		$row = (new \App\Db\Query())->from('vtiger_role')->where(['roleid' => static::$id])->one();

		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertSame($row['rolename'], 'Test edit');
		$this->assertSame((string) $row['changeowner'], '0');
		$this->assertSame($row['searchunpriv'], 'Contacts,Accounts');
		$this->assertSame($row['parentrole'], 'H1::' . static::$id);
		$this->assertSame((string) $row['allowassignedrecordsto'], '4');
		$this->assertSame((string) $row['clendarallorecords'], '2');
		$this->assertSame((string) $row['listrelatedrecord'], '1');
		$this->assertSame((string) $row['previewrelatedrecord'], '1');
		$this->assertSame((string) $row['editrelatedrecord'], '0');
		$this->assertSame($row['permissionsrelatedfield'], '0,1');
		$this->assertSame((string) $row['globalsearchadv'], '0');
		$this->assertSame((string) $row['assignedmultiowner'], '4');
		$this->assertSame((string) $row['auto_assign'], '0');
	}

	/**
	 * Testing role deletion.
	 */
	public function testDeleteRole()
	{
		$recordModel = \Settings_Roles_Record_Model::getInstanceById(static::$id);
		$transferToRole = \Settings_Roles_Record_Model::getInstanceById('H6');
		$this->assertNotNull($recordModel);
		$this->assertNotNull($transferToRole);
		$recordModel->delete($transferToRole);
		$this->assertFalse((new \App\Db\Query())->from('vtiger_role')->where(['roleid' => static::$id])->exists(), 'The record was not removed from the database ID: ' . static::$id);
	}
}
