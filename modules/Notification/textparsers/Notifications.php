<?php

/**
 * Notifications parser class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Notification_Notifications_TextParser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_NOTIFICATIONS';

	/** @var mixed Parser type */
	public $type = 'mail';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		$html = '';
		$scheduleData = \Vtiger_Watchdog_Model::getWatchingModulesSchedule($this->textParser->getParam('userId'), true);
		$modules = $scheduleData['modules'];

		$notificationInstance = \Notification_Module_Model::getInstance('Notification');
		$entries = \Notification_Module_Model::getEmailSendEntries($this->textParser->getParam('userId'), $modules, $this->textParser->getParam('startDate'), $this->textParser->getParam('endDate'));
		$pattern = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
		foreach ($notificationInstance->getTypes() as $typeId => $type) {
			if (isset($entries[$typeId])) {
				$html .= "<hr><strong>$type</strong><ul>";
				foreach ($entries[$typeId] as $notification) {
					$title = preg_replace_callback(
						$pattern, function ($matches) {
							return \AppConfig::main('site_URL') . $matches[0];
						}, $notification->getTitle());
					$massage = preg_replace_callback(
						$pattern, function ($matches) {
							return \AppConfig::main('site_URL') . $matches[0];
						}, $notification->getMessage());
					$html .= "<li>$title<br />$massage</li>";
				}
				$html .= '</ul><br />';
			}
		}
		if (empty($html)) {
			$html = \App\Language::translate('LBL_NO_NOTIFICATIONS', 'Notification');
		}
		return $html;
	}
}
