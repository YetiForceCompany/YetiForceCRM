<?php
/**
 * Cron state checker.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace App\SystemWarnings\SystemRequirements;

/**
 * Check if cron is enabled.
 */
class CronState extends \App\SystemWarnings\Template
{
	/**
	 * @var string Modal header title
	 */
	protected $title = 'LBL_CRON_STATE';
	/**
	 * @var int Warning priority code
	 */
	protected $priority = 9;

	/**
	 * Checks if cron is active.
	 */
	public function process()
	{
		$lastStart = \Settings_CronTasks_Module_Model::getInstance('Settings:CronTasks')->getLastCronStart();
		$checkTasks = (new \App\Db\Query())
			->select(['id'])
			->from('vtiger_cron_task')
			->where(['status' => 1, 'lastend' => null])
			->limit(1)->exists();
		$timeOut = round((\AppConfig::main('maxExecutionCronTime') + 300) / 60);
		if ($lastStart === 0 || $checkTasks || strtotime("-{$timeOut} minutes") > $lastStart) {
			$this->status = 0;
		} else {
			$this->status = 1;
		}
		if ($this->status === 0) {
			$this->link = $checkTasks ? 'https://yetiforce.com/en/knowledge-base/documentation/administrator-documentation/item/enable-cron' : 'index.php?module=CronTasks&parent=Settings&view=List';
			$this->linkTitle = \App\Language::translate($checkTasks ? 'LBL_HOW_TO_ENABLE_CRON' : 'LBL_CRON_TASKS_LIST', 'Settings:SystemWarnings');
			$this->description = \App\Language::translate($checkTasks ? 'LBL_CRON_DISABLED_DESC' : 'LBL_CRON_TASK_FAILED_DESC', 'Settings:SystemWarnings') . '<br />';
		}
	}
}
