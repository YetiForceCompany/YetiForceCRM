<?php
/**
 * Groups test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Arkadiusz Adach
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers Groups::<public>
 */
class Groups extends TestCase
{

	/**
	 * Group id
	 */
	private static $id;

	/**
	 * Testing groups creation
	 */
	public function testAddGroups()
	{
		$modules = [0 => '4', 1 => '7'];
		$recordModel = new Settings_Groups_Record_Model();
		$recordModel->set('groupname', 'Test groups');
		$recordModel->set('description', 'Test description');
		$recordModel->set('group_members', [0 => 'Users:1', 1 => 'Groups:2', 2 => 'Roles:H6', 3 => 'RoleAndSubordinates:H34']);
		$recordModel->set('modules', $modules);
		$recordModel->save();

		static::$id = $recordModel->getId();
		$this->assertInternalType('int', static::$id);

		$row = (new \App\Db\Query())->from('vtiger_groups')->where(['groupid' => static::$id])->one();

		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['groupname'], 'Test groups');
		$this->assertEquals($row['description'], 'Test description');

		$modulesFromDb = (new App\Db\Query())->from('vtiger_group2modules')->select('tabid')
				->where(['groupid' => static::$id])->column();

		$this->assertCount(count($modules), $modulesFromDb);
		foreach ($modules as $val) {
			$this->assertTrue(in_array($val, $modules));
		}

		$users2Group = (new App\Db\Query())->from('vtiger_users2group')->select('userid')
				->where(['groupid' => static::$id])->column();

		$this->assertCount(1, $users2Group);
		$this->assertEquals($users2Group[0], 1);

		$group2Grouprel = (new App\Db\Query())->from('vtiger_group2grouprel')->select('containsgroupid')
				->where(['groupid' => static::$id])->column();

		$this->assertCount(1, $group2Grouprel);
		$this->assertEquals($group2Grouprel[0], 2);

		$group2Rs = (new App\Db\Query())->from('vtiger_group2rs')->select('roleandsubid')
				->where(['groupid' => static::$id])->column();

		$this->assertCount(1, $group2Rs);
		$this->assertEquals($group2Rs[0], 'H34');

		$group2Role = (new App\Db\Query())->from('vtiger_group2role')->select('roleid')
				->where(['groupid' => static::$id])->column();

		$this->assertCount(1, $group2Role);
		$this->assertEquals($group2Role[0], 'H6');
	}

	/**
	 * Testing groups edit
	 */
	public function testEditGroups()
	{
		$recordModel = Settings_Groups_Record_Model::getInstance(static::$id);

		$modules = [0 => '7'];
		$recordModel->set('groupname', 'Test groups edit');
		$recordModel->set('description', 'Test description edit');
		$recordModel->set('group_members', [0 => 'Users:1']);
		$recordModel->set('modules', $modules);
		$recordModel->save();

		$row = (new \App\Db\Query())->from('vtiger_groups')->where(['groupid' => static::$id])->one();

		$this->assertEquals($row['groupname'], 'Test groups edit');
		$this->assertEquals($row['description'], 'Test description edit');

		$modulesFromDb = (new App\Db\Query())->from('vtiger_group2modules')->select('tabid')
				->where(['groupid' => static::$id])->column();

		$this->assertCount(count($modules), $modulesFromDb);
		foreach ($modules as $val) {
			$this->assertTrue(in_array($val, $modules));
		}

		$users2Group = (new App\Db\Query())->from('vtiger_users2group')->select('userid')
				->where(['groupid' => static::$id])->column();

		$this->assertCount(1, $users2Group);
		$this->assertEquals($users2Group[0], 1);

		$cnt = (new App\Db\Query())->from('vtiger_group2grouprel')->where(['groupid' => static::$id])->count();
		$this->assertEquals(0, $cnt);

		$cnt = (new App\Db\Query())->from('vtiger_group2rs')->where(['groupid' => static::$id])->count();
		$this->assertEquals(0, $cnt);

		$cnt = (new App\Db\Query())->from('vtiger_group2role')->where(['groupid' => static::$id])->count();
		$this->assertEquals(0, $cnt);
	}
}
