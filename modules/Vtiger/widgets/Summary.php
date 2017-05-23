<?php

/**
 * Vtiger summary widget class
 * @package YetiForce.Widget
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Vtiger_Summary_Widget extends Vtiger_Basic_Widget
{

	public function getWidget()
	{
		$this->Config['tpl'] = 'Summary.tpl';
		return $this->Config;
	}

	public function getConfigTplName()
	{
		return 'SummaryConfig';
	}
}
