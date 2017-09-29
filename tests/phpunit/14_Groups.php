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

	/**
	 * Testing groups creation
	 */
	public function testAddGroups()
	{
		$filename = 'testAddGroups.txt';
		file_put_contents($filename, '');

		$g_data = array(
			'groupname' => 'Test groups',
			'description' => 'Test description',
			'group_members' => NULL,
			'modules' => NULL,
		);

		$recordModel = new Settings_Groups_Record_Model();
		foreach ($g_data as $key => $value) {
			$recordModel->set($key, $value);
		}
		$recordModel->save();

		$id = $recordModel->getId();
		$this->assertInternalType('int', $id);

		$query = (new \App\Db\Query())
			->select('*')
			->from('vtiger_groups')
			->where(['groupid' => $id]);

		$row = $query->createCommand()->queryOne();
		file_put_contents($filename, var_export($row, true) . "\r\n", FILE_APPEND);

		$this->assertNotTrue($row === FALSE, "No record id: $id");

		file_put_contents($filename, var_export($row['groupname'], true) . "\r\n", FILE_APPEND);

		foreach ($g_data as $key => $value) {
			if ($row[$key] != $value) {
				file_put_contents($filename, var_export($row[$key], true) . "\r\n", FILE_APPEND);
			}
			$this->assertEquals($row[$key], $value);
		}
	}
}
