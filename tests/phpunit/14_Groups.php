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
	 * Vtiger_groups record id
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
		$recordModel->set('group_members', [0 => 'Users:1', 1 => 'Groups:2']);
		$recordModel->set('modules', $modules);
		$recordModel->save();

		static::$id = $recordModel->getId();
		$this->assertInternalType('int', static::$id);

		$row = (new \App\Db\Query())->from('vtiger_groups')->where(['groupid' => static::$id])->one();

		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['groupname'], 'Test groups');
		$this->assertEquals($row['description'], 'Test description');

		$modulesFromDb = [];
		$dataReader = (new App\Db\Query())
				->from('vtiger_group2modules')
				->where(['groupid' => static::$id])
				->createCommand()->query();
		while ($row = $dataReader->read()) {
			$modulesFromDb[] = $row['tabid'];
		}

		$this->assertCount(count($modules), $modulesFromDb);
		foreach ($modules as $val) {
			$this->assertTrue(in_array($val, $modules));
		}


		$users2Group = [];
		$dataReader = (new App\Db\Query())
				->from('vtiger_users2group')
				->where(['groupid' => static::$id])
				->createCommand()->query();
		while ($row = $dataReader->read()) {
			$users2Group[] = $row['userid'];
		}
		$this->assertCount(1, $users2Group);
		$this->assertEquals($users2Group[0], 1);


		$group2Grouprel = [];
		$dataReader = (new App\Db\Query())
				->from('vtiger_group2grouprel')
				->where(['groupid' => static::$id])
				->createCommand()->query();
		while ($row = $dataReader->read()) {
			$group2Grouprel[] = $row['containsgroupid'];
		}
		$this->assertCount(1, $group2Grouprel);
		$this->assertEquals($group2Grouprel[0], 2);
	}

	public function testEditGroups()
	{
		$lastId = static::$id;
		$recordModel = Settings_Groups_Record_Model::getInstance(static::$id);

		$recordModel->set('groupname', 'Test groups edit');
		$recordModel->set('description', 'Test description edit');
		$recordModel->set('group_members', [0 => 'Users:1']);
		$recordModel->set('modules', [0 => '7']);
		$recordModel->save();

		static::$id = $recordModel->getId();
		$this->assertInternalType('int', static::$id);
		$this->assertEquals(static::$id, $lastId);

		$row = (new \App\Db\Query())->from('vtiger_groups')->where(['groupid' => static::$id])->one();

		$this->assertNotFalse($row, 'No record id: ' . static::$id);
		$this->assertEquals($row['groupname'], 'Test groups edit');
		$this->assertEquals($row['description'], 'Test description edit');
	}
}
