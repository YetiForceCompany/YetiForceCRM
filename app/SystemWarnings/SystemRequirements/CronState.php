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
	protected $title = 'LBL_CRON_STATE';
	protected $priority = 9;

	/**
	 * Checks if cron is active.
	 */
	public function process()
	{
		$lastStart = \Settings_CronTasks_Module_Model::getInstance('Settings:CronTasks')->getLastCronStart();
		if ($lastStart === 0 || strtotime('-15 minutes') > $lastStart) {
			$this->status = 0;
		} else {
			$this->status = 1;
		}
		if ($this->status === 0) {
			$this->link = 'https://yetiforce.com/en/knowledge-base/documentation/administrator-documentation/item/enable-cron';
			$this->linkTitle = \App\Language::translate('LBL_HOW_TO_ENABLE_CRON', 'Settings:SystemWarnings');
			$this->description = \App\Language::translate('LBL_CRON_DISABLED_DESC', 'Settings:SystemWarnings') . '<br />';
		}
	}
}
