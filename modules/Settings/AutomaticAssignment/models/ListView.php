<?php
/*
 * Settings List View Model Class
 * @package YetiForce.Settings.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

class Settings_AutomaticAssignment_ListView_Model extends Settings_Vtiger_ListView_Model
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

	/**
	 * Function creates preliminary database query
	 * @return App\Db\Query()
	 */
	public function getBasicListQuery()
	{
		$module = $this->getModule();
		$query = (new App\Db\Query())->from($module->getBaseTable());
		$tabId = $this->get('sourceModule');
		if ($tabId) {
			$query->where(['tabid' => $tabId]);
		}
		return $query;
	}
}
