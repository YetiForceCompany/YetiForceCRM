<?php

/**
 * Vtiger PreviewMail widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_PreviewMail_Widget extends Vtiger_Basic_Widget
{
	public $allowedModules = ['OSSMailView'];
	public $dbParams = [];

	public function getUrl()
	{
		return 'module=' . $this->Module . '&view=Sview&noloadlibs=true&record=' . $this->Record;
	}

	public function getWidget()
	{
		$this->Config['url'] = $this->getUrl();

		return $this->Config;
	}

	public function getConfigTplName()
	{
		return 'PreviewMailConfig';
	}
}
