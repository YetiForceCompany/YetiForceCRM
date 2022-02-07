<?php

/**
 * Vtiger comments widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Comments_Widget extends Vtiger_Basic_Widget
{
	/**
	 * Params.
	 *
	 * @var string[]
	 */
	public $dbParams = ['relatedmodule' => 'ModComments'];

	/** {@inheritdoc} */
	public function isPermitted(): bool
	{
		return parent::isPermitted() && $this->moduleModel->isCommentEnabled();
	}

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
			$level = \App\ModuleHierarchy::getModuleLevel($this->Module);
			$this->Config['url'] = $this->getUrl();
			$this->Config['limit'] = $this->Data['limit'];
			$this->Config['level'] = $level;
			$this->Config['tpl'] = 'BasicComments.tpl';
			$widget = $this->Config;
		}
		return $widget;
	}
}
