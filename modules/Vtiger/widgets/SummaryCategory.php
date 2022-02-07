<?php

/**
 * Vtiger SummaryCategory widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_SummaryCategory_Widget extends Vtiger_Basic_Widget
{
	public function getWidget()
	{
		$this->Config['tpl'] = 'SummaryCategory.tpl';

		return $this->Config;
	}

	public function getConfigTplName()
	{
		return 'SummaryCategoryConfig';
	}
}
