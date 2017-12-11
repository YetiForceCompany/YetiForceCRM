<?php
/**
 * Countries test class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Wojciech Bruggemann <w.bruggemann@yetiforce.com>
 */
namespace Tests\Settings;

class Countries extends \Tests\Base
{

	/**
	 * Testing update all statuses
	 */
	public function testUpdateAllStatuses()
	{
		$this->updateAllStatuses(1);
		$this->updateAllStatuses(0);
	}

	/**
	 * Testing update sequence
	 */
	public function testUpdateSequence()
	{
		$moduleModel = new \Settings_Countries_Module_Model();
		$rows = $this->allRows();
		$keys = [];
		$values = [];
		foreach ($rows as $row) {
			$keys[] = $row['sortorderid'];
			$values[] = $row['id'];
		}
		$originKeys = $keys;
		shuffle($keys);
		$sequence = array_combine($keys, $values);
		$moduleModel->updateSequence($sequence);
		$rows2 = $this->allRows();
		$this->assertTrue($rows !== $rows2);

		$sequence = array_combine($originKeys, $values);
		$moduleModel->updateSequence($sequence);
		$rows3 = $this->allRows();
		$this->assertTrue($rows === $rows3);
	}

	public function testUpdateStatus()
	{
		$moduleModel = new \Settings_Countries_Module_Model();
		$row = (new \App\Db\Query())->from('u_#__countries')->one();
		$id = $row['id'];
		$status = $row['status'] ? 0 : 1;
		$result = $moduleModel->updateStatus($id, $status);
		$this->assertGreaterThan(0, $result);
		$status2 = $this->scalarOfField($id, 'status');
		$this->assertEquals($status, $status2);
	}

	public function testUpdatePhone()
	{
		$moduleModel = new \Settings_Countries_Module_Model();
		$row = (new \App\Db\Query())->from('u_#__countries')->one();
		$id = $row['id'];
		$phone = $row['phone'] ? 0 : 1;
		$result = $moduleModel->updatePhone($id, $phone);
		$this->assertGreaterThan(0, $result);
		$status2 = $this->scalarOfField($id, 'phone');
		$this->assertEquals($phone, $status2);
	}

	public function testUpdateUitype()
	{
		$moduleModel = new \Settings_Countries_Module_Model();
		$row = (new \App\Db\Query())->from('u_#__countries')->one();
		$id = $row['id'];
		$uitype = $row['uitype'] ? 0 : 1;
		$result = $moduleModel->updateUitype($id, $uitype);
		$this->assertGreaterThan(0, $result);
		$status2 = $this->scalarOfField($id, 'uitype');
		$this->assertEquals($uitype, $status2);
	}

	protected function scalarOfField($id, $fieldName)
	{
		return (new \App\Db\Query())->from('u_#__countries')->select($fieldName)->where(['id' => $id])->scalar();
	}

	protected function allRows()
	{
		return (new \App\Db\Query())->from('u_#__countries')->all();
	}

	protected function updateAllStatuses($status)
	{
		$moduleModel = new \Settings_Countries_Module_Model();
		$moduleModel->updateAllStatuses($status);
		$exists = (new \App\Db\Query())->from('u_#__countries')->where(['status' => (int) !$status])->exists();
		$this->assertFalse($exists);
	}
}
