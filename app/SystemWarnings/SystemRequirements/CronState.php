<?php
/**
 * Cron state checker.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	 *
	 * @return void
	 */
	public function process(): void
	{
		$lastStart = \Settings_CronTasks_Module_Model::getInstance('Settings:CronTasks')->getLastCronStart();
		$checkTasks = (new \App\Db\Query())
			->select(['id'])
			->from('vtiger_cron_task')
			->where(['status' => 1, 'lastend' => null])
			->limit(1)->exists();
		$timeOut = round((\App\Config::main('maxExecutionCronTime') + 300) / 60);
		if (0 === $lastStart || $checkTasks || strtotime("-{$timeOut} minutes") > $lastStart) {
			$this->status = 0;
		} else {
			$this->status = 1;
		}
		if (0 === $this->status) {
			if (!$checkTasks && \App\Security\AdminAccess::isPermitted('CronTasks')) {
				$this->link = 'index.php?module=CronTasks&parent=Settings&view=List';
				$this->linkTitle = \App\Language::translate('LBL_CRON_TASKS_LIST', 'Settings:SystemWarnings');
			} elseif ($checkTasks) {
				$this->link = 'https://yetiforce.com/en/knowledge-base/documentation/administrator-documentation/item/enable-cron';
				$this->linkTitle = \App\Language::translate('LBL_HOW_TO_ENABLE_CRON', 'Settings:SystemWarnings');
			}
			$this->description = \App\Language::translate($checkTasks ? 'LBL_CRON_DISABLED_DESC' : 'LBL_CRON_TASK_FAILED_DESC', 'Settings:SystemWarnings') . '<br />';
		}
	}
}
