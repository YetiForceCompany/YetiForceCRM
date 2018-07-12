<?php
/**
 * Calendar test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\Settings;

class Calendar extends \Tests\Base
{
	/**
	 * Testing calendar config save.
	 */
	public function testUpdateCalendarConfig()
	{
		\Settings_Calendar_Module_Model::updateCalendarConfig(['color' => 1, 'id' => 'update_event']);
		$result = \Settings_Calendar_Module_Model::getCalendarConfig('reminder');
		$found = false;
		foreach ($result as $row) {
			if ($row['name'] === 'update_event') {
				$this->assertSame((int) $row['value'], 1);
				$found = true;
			}
		}
		$this->assertTrue($found);
	}

	/**
	 * Testing not working days config save.
	 */
	public function testUpdateNotWorkingDays()
	{
		$referenceData = ['1', '3'];
		\Settings_Calendar_Module_Model::updateNotWorkingDays(['val' => $referenceData]);
		$result = \Settings_Calendar_Module_Model::getNotWorkingDays();
		$this->assertSame($result, $referenceData);
	}

	/**
	 * Testing getPicklistValue method.
	 */
	public function testGetPicklistValue()
	{
		\App\Db::getInstance()->createCommand()->insert('vtiger_activitytype', ['activitytype'=>'UnitTestCalendar', 'presence'=>1, 'picklist_valueid'=>99999, 'sortorderid' => 99, 'color'=>'A0B584'])->execute();
		$this->assertTrue((count(\Settings_Calendar_Module_Model::getPicklistValue()) > 0));
	}

	/**
	 * Reset to default values.
	 */
	public function testResetToDefault()
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_activitytype', ['activitytype' => 'UnitTestCalendar'])->execute();
		\Settings_Calendar_Module_Model::updateCalendarConfig(['color' => 0, 'id' => 'update_event']);
		$result = \Settings_Calendar_Module_Model::getCalendarConfig('reminder');
		$found = false;
		foreach ($result as $row) {
			if ($row['name'] === 'update_event') {
				$this->assertSame((int) $row['value'], 0);
				$found = true;
			}
		}
		$this->assertTrue($found);

		$referenceNotWorkingDays = [];
		\Settings_Calendar_Module_Model::updateNotWorkingDays(['val' => $referenceNotWorkingDays]);
		$result = \Settings_Calendar_Module_Model::getNotWorkingDays();
		$this->assertSame($result, $referenceNotWorkingDays);
	}
}
