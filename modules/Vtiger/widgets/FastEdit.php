<?php

/**
 * Vtiger FastEdit widget class
 * @package YetiForce.Widget
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Vtiger_FastEdit_Widget extends Vtiger_Basic_Widget
{

	public function getUrl()
	{
		return 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=getActivities&page=1&limit=' . $this->Data['limit'];
	}

	public function getConfigTplName()
	{
		return 'FastEditConfig';
	}

	public function getWidget()
	{
		$this->Config['tpl'] = 'FastEdit.tpl';
		$this->Config['url'] = $this->getUrl();
		return $this->Config;
	}
}
