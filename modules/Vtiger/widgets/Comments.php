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
	 * Params
	 * @var array
	 */
	public $dbParams = ['relatedmodule' => 'ModComments'];

	/**
	 * Return url
	 * @return string
	 */
	public function getUrl()
	{
		return 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showRecentComments&page=1&limit=' . $this->Data['limit'];
	}

	/**
	 * Function return config template name
	 */
	public function getConfigTplName()
	{
		return 'CommentsConfig';
	}

	/**
	 * Function return
	 * @return array
	 */
	public function getWidget()
	{
		$widget = [];
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		if ($this->moduleModel->isCommentEnabled() && $modCommentsModel->isPermitted('EditView')) {
			$hierarchyList = ['LBL_COMMENTS_0', 'LBL_COMMENTS_1', 'LBL_COMMENTS_2'];
			$level = \App\ModuleHierarchy::getModuleLevel($this->Module);
			if ($level > 0) {
				unset($hierarchyList[1]);
				if ($level > 1) {
					unset($hierarchyList[2]);
				}
			}
			$this->Config['hierarchy'] = [];
			$this->Config['hierarchyList'] = $hierarchyList;
			$this->Config['url'] = $this->getUrl();
			$this->Config['tpl'] = 'BasicComments.tpl';
			$widget = $this->Config;
		}
		return $widget;
	}
}
