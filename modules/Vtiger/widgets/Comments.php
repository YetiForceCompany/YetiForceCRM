<?php

/**
 * Vtiger comments widget class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Comments_Widget extends Vtiger_Basic_Widget
{
	/**
	 * Params.
	 *
	 * @var string[]
	 */
	public $dbParams = ['relatedmodule' => 'ModComments'];

	/**
	 * Return url.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showRecentComments&page=1&limit=' . $this->Data['limit'];
	}

	/**
	 * Function return config template name.
	 */
	public function getConfigTplName()
	{
		return 'CommentsConfig';
	}

	/**
	 * Function return.
	 *
	 * @return array
	 */
	public function getWidget()
	{
		$widget = [];
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		if ($this->moduleModel->isCommentEnabled() && $modCommentsModel->isPermitted('EditView')) {
			$this->Config['switchHeader'] = [];
			$level = \App\ModuleHierarchy::getModuleLevel($this->Module);
			if ($level === 0) {
				$this->Config['switchHeader']['on'] = \App\Json::encode([0]);
				$this->Config['switchHeader']['off'] = \App\Json::encode([1, 2]);
			} elseif ($level === 1) {
				$this->Config['switchHeader']['on'] = \App\Json::encode([1]);
				$this->Config['switchHeader']['off'] = \App\Json::encode([2]);
			}

			$this->Config['switchHeaderLables']['on'] = \App\Language::translate('LBL_COMMENTS_0', 'ModComments');
			$this->Config['switchHeaderLables']['off'] = \App\Language::translate('LBL_ALL_RECORDS', 'ModComments');
			$this->Config['url'] = $this->getUrl();
			$this->Config['level'] = $level;
			$this->Config['tpl'] = 'BasicComments.tpl';
			$widget = $this->Config;
		}
		return $widget;
	}
}
