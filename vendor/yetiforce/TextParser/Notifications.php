<?php
namespace App\TextParser;

/**
 * Notifications parser class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Notifications extends Base
{

	/** @var string Class name */
	public $name = 'LBL_NOTIFICATIONS';

	/** @var array Allowed modules */
	public $allowedModules = ['Notification'];

	/**
	 * Process
	 * @return string
	 */
	public function process()
	{
		$siteURL = \AppConfig::main('site_URL');
		$html = '';
		$scheduleData = \Vtiger_Watchdog_Model::getWatchingModulesSchedule($this->textParser->getParam('userId'), true);
		$modules = $scheduleData['modules'];

		$notificationInstance = \Notification_Module_Model::getInstance('Notification');
		$entries = \Notification_Module_Model::getEmailSendEntries($this->textParser->getParam('userId'), $modules, $this->textParser->getParam('startDate'), $this->textParser->getParam('endDate'));
		foreach ($notificationInstance->getTypes() as $typeId => $type) {
			if (isset($entries[$typeId])) {
				$html .= "<hr><strong>$type</strong><ul>";
				foreach ($entries[$typeId] as $notification) {
					$title = \vtlib\Functions::replaceLinkAddress($notification->getTitle(), '/^index.php/', $siteURL . 'index.php');
					$massage = \vtlib\Functions::replaceLinkAddress($notification->getMessage(), '/^index.php/', $siteURL . 'index.php');
					$html .= "<li>$title<br>$massage</li>";
				}
				$html .= '</ul><br>';
			}
		}
		if (empty($html)) {
			$html = \App\Language::translate('LBL_NO_NOTIFICATIONS', 'Notification');
		}
		return $html;
	}
}
