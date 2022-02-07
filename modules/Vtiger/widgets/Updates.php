<?php

/**
 * Vtiger Updates widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Updates_Widget extends Vtiger_Basic_Widget
{
	public function getUrl()
	{
		return 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showRecentActivities&page=1&limit=5&skipHeader=true';
	}

	public function getWidget()
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleName = 'ModTracker';
		$this->Config['tpl'] = 'Updates.tpl';
		$this->Config['moduleBaseName'] = $moduleName;
		$this->Config['url'] = $this->getUrl();
		$this->Config['newChanege'] = ModTracker_Record_Model::isNewChange($this->Record, $currentUser->getRealId());
		$this->Config['switchHeader'] = [];
		$this->Config['switchHeader']['on'] = 'changes';
		$this->Config['switchHeader']['off'] = 'review';
		$this->Config['switchHeaderLables']['on'] = \App\Language::translate('LBL_UPDATES', $moduleName);
		$this->Config['switchHeaderLables']['off'] = \App\Language::translate('LBL_REVIEW_HISTORY', $moduleName);

		return $this->Config;
	}
}
