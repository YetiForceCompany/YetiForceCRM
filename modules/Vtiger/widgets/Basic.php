<?php

/**
 * Vtiger basic widget class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Basic_Widget
{
	public $Module = false;
	public $Record = false;
	public $Config = [];
	public $moduleModel = false;
	public $dbParams = [];
	public $allowedModules = [];

	public function __construct($Module = false, $moduleModel = false, $Record = false, $widget = [])
	{
		$this->Module = $Module;
		$this->Record = $Record;
		$this->Config = $widget;
		$this->Config['tpl'] = 'Basic.tpl';
		$this->Data = $widget['data'] ?? [];
		$this->moduleModel = $moduleModel;
	}

	public function getConfigTplName()
	{
		return 'BasicConfig';
	}

	public function getWidget()
	{
		return $this->Config;
	}
}
