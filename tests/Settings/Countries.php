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
		$rows = $this->allRows();
		$keys = [];
		$values = [];
		foreach ($rows as $row) {
			$keys[] = $row['sortorderid'];
			$values[] = $row['id'];
		}
		shuffle($keys);
		$sequence = array_combine($keys, $values);
		$moduleModel = new \Settings_Countries_Module_Model();
		$moduleModel->updateSequence($sequence);
		$rows2 = $this->allRows();
		$this->assertTrue($rows !== $rows2);
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
