<?php

/**
 * Vtiger PreviewMail widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_PreviewMail_Widget extends Vtiger_Basic_Widget
{
	/** {@inheritdoc} */
	public $allowedModules = ['OSSMailView'];
	public $dbParams = [];

	/**
	 * Get URL.
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return 'module=' . $this->Module . '&view=Sview&noloadlibs=true&record=' . $this->Record;
	}

	/** {@inheritdoc} */
	public function getWidget()
	{
		$this->Config['url'] = $this->getUrl();
		$this->Config['tpl'] = 'PreviewMail.tpl';

		return $this->Config;
	}

	/** {@inheritdoc} */
	public function getConfigTplName()
	{
		return 'PreviewMailConfig';
	}
}
