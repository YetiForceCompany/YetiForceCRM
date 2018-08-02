<?php
/**
 * CronTasks test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\Settings;

class CronTasks extends \Tests\Base
{
	/**
	 * Testing last cron iteration getter.
	 */
	public function testGetLastCronIteration()
	{
		$module = \Settings_CronTasks_Module_Model::getInstance('Settings:CronTasks');
		$this->assertNotEmpty($module->getLastCronIteration(), 'Last cron iteration should be not empty');
	}

	/**
	 * Testing cron tasks sequence update.
	 */
	public function testUpdateTasksSequence()
	{
		$moduleModel = \Settings_CronTasks_Module_Model::getInstance('Settings:CronTasks');
		$dbSequence = $testSequence = (new \App\Db\Query())->select(['sequence', 'id'])->from('vtiger_cron_task')->orderBy('sequence ASC')->createCommand()->queryAllByGroup(0);

		$tmp = $testSequence[1];
		$testSequence[1] = $testSequence[3];
		$testSequence[3] = $tmp;
		$moduleModel->updateSequence($testSequence);
		$currentSequence = (new \App\Db\Query())->select(['sequence', 'id'])->from('vtiger_cron_task')->orderBy('sequence ASC')->createCommand()->queryAllByGroup(0);
		$this->assertSame($testSequence, $currentSequence, 'Current sequence is different from provided');
		$moduleModel->updateSequence($dbSequence);
	}

	/**
	 * Testing record instance functions.
	 */
	public function testRecordInstance()
	{
		$recordModel = \Settings_CronTasks_Record_Model::getInstanceByName('LBL_BROWSING_HISTORY');
		$this->AssertSame('LBL_BROWSING_HISTORY', $recordModel->getName());
		foreach (['frequency', 'status', 'name', 'duration']as $fieldName) {
			$this->AssertNotEmpty($recordModel->getDisplayValue($fieldName), 'Field ' . $fieldName . ' returned empty value');
		}
		$this->AssertInternalType('int', $recordModel->getTimeDiff(), 'Returned time difference should be integer');
		$this->AssertNotEmpty($recordModel->getDuration(), 'Returned duration should be not empty');
	}
}
