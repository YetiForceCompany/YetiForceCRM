<?php

/**
 * ListView model for Notification module
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Notification_ListView_Model extends Vtiger_ListView_Model
{

	/**
	 * Function to get the Quick Links for the List view of the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getHederLinks($linkParams)
	{
		$links = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), ['LIST_VIEW_HEADER'], $linkParams);
		$headerLinks = [];
		$moduleModel = $this->getModule();

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($userPrivilegesModel->hasModulePermission('Notification') && $userPrivilegesModel->hasModuleActionPermission('Notification', 'NotificationCreateMessage')) {
			$headerLinks[] = [
				'linktype' => 'LIST_VIEW_HEADER',
				'linkhint' => 'LBL_SEND_NOTIFICATION',
				'linkurl' => 'index.php?module=Notification&view=NotificationConfig',
				'linkicon' => 'glyphicon glyphicon-cog',
				'modalView' => true
			];
		}
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($userPrivilegesModel->hasModulePermission('Notification') && $userPrivilegesModel->hasModuleActionPermission('Notification', 'NotificationCreateMessage')) {
			$headerLinks[] = [
				'linktype' => 'LIST_VIEW_HEADER',
				'linkhint' => 'LBL_SEND_NOTIFICATION',
				'linkurl' => 'javascript:Vtiger_Index_Js.sendNotification(this)',
				'linkicon' => 'glyphicon glyphicon-send'
			];
		}
		foreach ($headerLinks as $headerLink) {
			$links['LIST_VIEW_HEADER'][] = Vtiger_Link_Model::getInstanceFromValues($headerLink);
		}
		return $links;
	}
	/*
	 * Function to get Basic links
	 * @return array of Basic links
	 */

	public function getBasicLinks()
	{
		return [];
	}
}
