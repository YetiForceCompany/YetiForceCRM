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
			if ('update_event' === $row['name']) {
				$this->assertSame((int) $row['value'], 1, 'Calendar config value is different than provided');
				$found = true;
			}
		}
		$this->assertTrue($found, 'Calendar config option not found');
	}

	/**
	 * Testing not working days config save.
	 */
	public function testUpdateNotWorkingDays()
	{
		$referenceData = ['1', '3'];
		\Settings_Calendar_Module_Model::updateNotWorkingDays(['val' => $referenceData]);
		$result = \Settings_Calendar_Module_Model::getNotWorkingDays();
		$this->assertSame($result, $referenceData, 'Not working days differs from provided');
	}

	/**
	 * Testing getPicklistValue method.
	 */
	public function testGetPicklistValue()
	{
		\App\Db::getInstance()->createCommand()->insert('vtiger_activitytype', ['activitytype' => 'UnitTestCalendar', 'presence' => 1,  'sortorderid' => 99, 'color' => 'A0B584'])->execute();
		\App\Cache::clear();
		$this->assertGreaterThan(0, (\count(\Settings_Calendar_Module_Model::getPicklistValue())), 'Calendar activity type picklist is empty');
	}

	/**
	 * Reset to default values.
	 */
	public function testResetToDefault()
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_activitytype', ['activitytype' => 'UnitTestCalendar'])->execute();
		\App\Cache::clear();
		\Settings_Calendar_Module_Model::updateCalendarConfig(['color' => 0, 'id' => 'update_event']);
		$result = \Settings_Calendar_Module_Model::getCalendarConfig('reminder');
		$found = false;
		foreach ($result as $row) {
			if ('update_event' === $row['name']) {
				$this->assertSame((int) $row['value'], 0, 'Calendar config value is different than provided');
				$found = true;
			}
		}
		$this->assertTrue($found, 'Calendar config option not found');

		$referenceNotWorkingDays = [];
		\Settings_Calendar_Module_Model::updateNotWorkingDays(['val' => $referenceNotWorkingDays]);
		$result = \Settings_Calendar_Module_Model::getNotWorkingDays();
		$this->assertSame($result, $referenceNotWorkingDays, 'Not working days should be empty');
	}
}
