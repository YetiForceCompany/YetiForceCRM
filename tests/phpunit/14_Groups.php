<?php
/**
 * AddUser test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

class Groups extends TestCase
{

	private $id;

	/**
	 * Testing groups creation
	 */
	public function testAddGroups()
	{
		$gData = array(
			'groupname' => 'Test groups',
			'description' => 'Test description',
			'group_members' => array(0 => 'Users:1', 1 => 'Groups:2'),
			'modules' => array(0 => '4', 1 => '7')
		);

		$recordModel = new Settings_Groups_Record_Model();
		foreach ($gData as $key => $value) {
			$recordModel->set($key, $value);
		}
		$recordModel->save();

		$id = $recordModel->getId();
		$this->id = $id;
		$this->assertInternalType('int', $id);

		$row = (new \App\Db\Query())->from('vtiger_groups')->where(['groupid' => $id])->one();

		$this->assertNotFalse($row, "No record id: $id");
		$this->assertEquals($row['groupname'], $gData['groupname']);
		$this->assertEquals($row['description'], $gData['description']);
	}
}
