<?php
/**
 * Groups test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Tests\Settings;

class Groups extends \Tests\Base
{
	/**
	 * Group id.
	 */
	private static $id;

	/**
	 * Testing groups creation.
	 */
	public function testAddGroups()
	{
		$modules = ['4', '7'];
		$recordModel = new \Settings_Groups_Record_Model();
		$recordModel->set('groupname', 'Test groups');
		$recordModel->set('description', 'Test description');
		$recordModel->set('group_members', ['Users:1', 'Groups:2', 'Roles:H6', 'RoleAndSubordinates:H34']);
		$recordModel->set('modules', $modules);
		$recordModel->save();

		static::$id = $recordModel->getId();
		$this->assertInternalType('int', static::$id);

		$row = (new \App\Db\Query())->from('vtiger_groups')->where(['groupid' => static::$id])->one();

		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertSame($row['groupname'], 'Test groups');
		$this->assertSame($row['description'], 'Test description');

		$modulesFromDb = (new \App\Db\Query())->from('vtiger_group2modules')->select('tabid')->where(['groupid' => static::$id])->column();
		$this->assertCount(0, array_diff($modules, $modulesFromDb));

		$users2Group = (new \App\Db\Query())->from('vtiger_users2group')->select('userid')->where(['groupid' => static::$id])->column();
		$this->assertCount(1, $users2Group);
		$this->assertSame($users2Group[0], 1);

		$group2Grouprel = (new \App\Db\Query())->from('vtiger_group2grouprel')->select('containsgroupid')->where(['groupid' => static::$id])->column();
		$this->assertCount(1, $group2Grouprel);
		$this->assertSame($group2Grouprel[0], 2);

		$group2Rs = (new \App\Db\Query())->from('vtiger_group2rs')->select('roleandsubid')->where(['groupid' => static::$id])->column();
		$this->assertCount(1, $group2Rs);
		$this->assertSame($group2Rs[0], 'H34');

		$group2Role = (new \App\Db\Query())->from('vtiger_group2role')->select('roleid')->where(['groupid' => static::$id])->column();
		$this->assertCount(1, $group2Role);
		$this->assertSame($group2Role[0], 'H6');
	}

	/**
	 * Testing groups edit.
	 */
	public function testEditGroups()
	{
		$recordModel = \Settings_Groups_Record_Model::getInstance(static::$id);

		$modules = ['7'];
		$recordModel->set('groupname', 'Test groups edit');
		$recordModel->set('description', 'Test description edit');
		$recordModel->set('group_members', ['Users:1']);
		$recordModel->set('modules', $modules);
		$recordModel->save();

		$row = (new \App\Db\Query())->from('vtiger_groups')->where(['groupid' => static::$id])->one();

		$this->assertSame($row['groupname'], 'Test groups edit');
		$this->assertSame($row['description'], 'Test description edit');

		$modulesFromDb = (new \App\Db\Query())->from('vtiger_group2modules')->select('tabid')
			->where(['groupid' => static::$id])->column();

		$this->assertCount(0, array_diff($modules, $modulesFromDb));

		$users2Group = (new \App\Db\Query())->from('vtiger_users2group')->select('userid')
			->where(['groupid' => static::$id])->column();

		$this->assertCount(1, $users2Group);
		$this->assertSame($users2Group[0], 1);

		$this->assertFalse((new \App\Db\Query())->from('vtiger_group2grouprel')->where(['groupid' => static::$id])->exists(), 'Record in the database should not exist');
		$this->assertFalse((new \App\Db\Query())->from('vtiger_group2rs')->where(['groupid' => static::$id])->exists(), 'Record in the database should not exist');
		$this->assertFalse((new \App\Db\Query())->from('vtiger_group2role')->where(['groupid' => static::$id])->exists(), 'Record in the database should not exist');
	}

	/**
	 * Testing group deletion.
	 */
	public function testDeleteGroups()
	{
		$recordModel = \Settings_Groups_Record_Model::getInstance(static::$id);

		$transferRecordId = 1;
		$transferToOwner = \Settings_Groups_Record_Model::getInstance($transferRecordId);
		if (!$transferToOwner) {
			$transferToOwner = \Users_Record_Model::getInstanceById($transferRecordId, 'Users');
		}

		$recordModel->delete($transferToOwner);
		$this->assertFalse((new \App\Db\Query())->from('vtiger_groups')->where(['groupid' => static::$id])->exists(), 'The record was not removed from the database ID: ' . static::$id);
	}
}
