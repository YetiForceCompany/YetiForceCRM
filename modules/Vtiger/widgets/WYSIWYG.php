<?php

/**
 * Vtiger WYSIWYG widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_WYSIWYG_Widget extends Vtiger_Basic_Widget
{
	public function getWidget()
	{
		$this->Config['tpl'] = 'WYSIWYG.tpl';

		return $this->Config;
	}

	public function getConfigTplName()
	{
		return 'WYSIWYGConfig';
	}
}
