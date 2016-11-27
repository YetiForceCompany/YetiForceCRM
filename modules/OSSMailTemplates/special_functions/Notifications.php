<?php

/**
 * Notifications Class - special function for mail templates
 * @package YetiForce.MailTemplatesSpecialFunctions
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Notifications
{

	private $moduleList = ['all'];

	/**
	 * Function executes the sending notifications action
	 * @param array $data
	 * @return string
	 */
	public function process($data)
	{
		$siteURL = AppConfig::main('site_URL');
		$html = '';
		$modules = [];
		if ($data['module'] == 'System') {
			$scheduleData = Vtiger_Watchdog_Model::getWatchingModulesSchedule($data['userId'], true);
			$modules = $scheduleData['modules'];
		} else {
			$modules[] = $data['module'];
		}
		$notificationInstance = Notification_Module_Model::getInstance('Notification');
		$entries = Notification_Module_Model::getEmailSendEntries($data['userId'], $modules, $data['startDate'], $data['endDate']);
		foreach ($notificationInstance->getTypes() as $typeId => $type) {
			if (isset($entries[$typeId])) {
				$html .= '<hr><strong>' . $type . '</strong><ul>';
				foreach ($entries[$typeId] as $notification) {
					$title = vtlib\Functions::replaceLinkAddress($notification->getTitle(), '/^index.php/', $siteURL . 'index.php');
					$massage = vtlib\Functions::replaceLinkAddress($notification->getMessage(), '/^index.php/', $siteURL . 'index.php');
					$html .= '<li>' . $title . '<br>' . $massage . '</li>';
				}
				$html .= '</ul><br>';
			}
		}
		if (empty($html)) {
			$html = vtranslate('LBL_NO_NOTIFICATIONS', 'Home');
		}
		return $html;
	}

	public function getListAllowedModule()
	{
		return $this->moduleList;
	}
}
