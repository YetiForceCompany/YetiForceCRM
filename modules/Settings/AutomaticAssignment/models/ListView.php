<?php
/*
 * Settings List View Model Class
 * @package YetiForce.Settings.Model
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

class Settings_AutomaticAssignment_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/**
	 * Function to get Basic links.
	 *
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
				'linkurl' => $this->getModule()->getCreateRecordUrl(),
				'linkicon' => 'fas fa-plus',
				'linkclass' => 'btn-light',
				'showLabel' => '1'
			];
		}
		return $basicLinks;
	}

	/** {@inheritdoc} */
	public function getBasicListQuery(): App\Db\Query
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
