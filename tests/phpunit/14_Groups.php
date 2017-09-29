<?php
/**
 * AddUser test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers Groups::<public>
 */
class Groups extends TestCase
{

	/**
	 * Testing groups creation
	 */
	public function testAddGroups()
	{
		$recordModel = new Settings_Groups_Record_Model();
		$recordModel->set('groupname', 'Test groups');
		$recordModel->set('description', 'Test description');
		$recordModel->set('group_members', [0 => 'Users:1', 1 => 'Groups:2']);
		$recordModel->set('modules', [0 => '4', 1 => '7']);
		$recordModel->save();

		$id = $recordModel->getId();
		$this->assertInternalType('int', $id);
		$row = (new \App\Db\Query())->from('vtiger_groups')->where(['groupid' => $id])->one();
		$this->assertNotFalse($row, "No record id: $id");
		$this->assertEquals($row['groupname'], 'Test groups');
		$this->assertEquals($row['description'], 'Test description');
	}
}
