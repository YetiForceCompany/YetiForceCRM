<?php

/**
 * Vtiger detail view widget class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_DetailView_Widget extends Vtiger_Basic_Widget
{
	/**
	 * Return url.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showModuleDetailView';
	}

	/**
	 * Function return config template name.
	 *
	 * @return string
	 */
	public function getConfigTplName()
	{
		return 'DetailViewConfig';
	}

	/**
	 * Function return widget.
	 *
	 * @return array
	 */
	public function getWidget()
	{
		$widget = [];
		$moduleName = $this->Module;
		$model = Vtiger_Module_Model::getInstance($moduleName);
		if ($model->isPermitted($moduleName)) {
			$this->Config['url'] = $this->getUrl();
			$this->Config['tpl'] = 'DetailView.tpl';
			$widget = $this->Config;
		}
		return $widget;
	}
}
