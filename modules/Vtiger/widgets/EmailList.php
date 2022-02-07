<?php

/**
 * Vtiger EmailList widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_EmailList_Widget extends Vtiger_Basic_Widget
{
	/**
	 * Params.
	 *
	 * @var string[]
	 */
	public $dbParams = ['relatedmodule' => 'OSSMailView'];

	public function getUrl()
	{
		return 'module=OSSMailView&view=Widget&smodule=' . $this->Module . '&srecord=' . $this->Record . '&mode=showEmailsList&type=All&mailFilter=All&limit=' . $this->Data['limit'];
	}

	public function getConfigTplName()
	{
		return 'EmailListConfig';
	}

	public function getWidget()
	{
		$widget = [];
		$model = Vtiger_Module_Model::getInstance('OSSMailView');
		if ($model->isPermitted('DetailView')) {
			$this->Config['tpl'] = 'EmailList.tpl';
			$this->Config['url'] = $this->getUrl();
			$widget = $this->Config;
		}
		return $widget;
	}
}
