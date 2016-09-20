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

	public function process($data)
	{
		$siteURL = vglobal('site_URL');
		$html = '';
		$conditions = '';
		$modules = [];
		if ($data['module'] == 'System') {
			$watchingModules = Vtiger_Watchdog_Model::getWatchingModules(false, $data['userId']);
			foreach ($watchingModules as $moduleId) {
				$modules[] = vtlib\Functions::getModuleName($moduleId);
			}
			$modules[] = 'Users';
		} else {
			$modules[] = $data['module'];
		}
		$conditions = $this->getNotificationsConditions($data, $modules);

		$notificationInstance = Home_Notification_Model::getInstance();
		$entries = $notificationInstance->getEntries(false, $conditions, $data['userId']);
		foreach ($notificationInstance->getTypes() as $typeId => $type) {
			if (array_key_exists($typeId, $entries)) {
				$html .= '<hr><strong>' . vtranslate($type['name'], 'Home') . '</strong><ul>';
				foreach ($entries[$typeId] as $notification) {
					$title = vtlib\Functions::replaceLinkAddress($notification->getTitle(), '/^index.php/', $siteURL . 'index.php');
					$massage = vtlib\Functions::replaceLinkAddress($notification->getMassage(), '/^index.php/', $siteURL . 'index.php');
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

	public function getNotificationsConditions($data, $modules)
	{
		$db = PearDatabase::getInstance();
		$conditions = '';
		if (!empty($modules)) {
			if (!is_array($modules)) {
				$modules = [$modules];
			}
			$conditions .= ' && reletedmodule IN ("' . implode('","', $modules) . '")';
		}
		if (!empty($data['endDate']) && !empty($data['startDate'])) {
			$conditions .= ' && `time` BETWEEN ' . $db->sql_escape_string($data['startDate']) . ' AND ' . $db->sql_escape_string($data['endDate']);
		} elseif (!empty($data['endDate'])) {
			$conditions .= ' && `time` <= ' . $db->sql_escape_string($data['endDate']);
		}
		return $conditions;
	}

	public function getListAllowedModule()
	{
		return $this->moduleList;
	}
}
