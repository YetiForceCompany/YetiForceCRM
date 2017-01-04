<?php

/**
 * List View Model Class for MailSmtp Settings
 * @package YetiForce.Settings.Record
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */

class Settings_MailSmtp_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/**
	 * Function to get Basic links
	 * @return array of Basic links
	 */
	public function getBasicLinks()
	{
		$basicLinks = [];
		$moduleModel = $this->getModule();
		if ($moduleModel->hasCreatePermissions()) {
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_RECORD',
				'linkdata' => ['url' => $moduleModel->getCreateRecordUrl()],
				'linkicon' => 'glyphicon glyphicon-plus',
				'linkclass' => 'btn-success addRecord',
				'showLabel' => '1'
			];
		}
		return $basicLinks;
	}

}
