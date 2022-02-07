<?php

/**
 * Vtiger basic widget class.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Basic_Widget
{
	public $Module = false;
	public $Record = false;
	public $Config = [];
	public $moduleModel = false;
	public $dbParams = [];
	public $allowedModules = [];

	public function __construct($Module = false, $moduleModel = null, $Record = null, $widget = [])
	{
		$this->Module = $Module;
		$this->Record = $Record;
		$this->Config = $widget;
		$this->Config['tpl'] = 'Basic.tpl';
		$this->Data = $widget['data'] ?? [];
		$this->moduleModel = $moduleModel ?: \Vtiger_Module_Model::getInstance($this->Module);
	}

	/**
	 * Function to check permission.
	 *
	 * @return bool
	 */
	public function isPermitted(): bool
	{
		return !$this->allowedModules || \in_array($this->Module, $this->allowedModules);
	}

	public function getConfigTplName()
	{
		return 'BasicConfig';
	}

	public function getWidget()
	{
		$widget = $this->Config;
		$widget['instance'] = $this;
		return $widget;
	}

	/**
	 * Return data for api.
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	public function getApiData(array $row): array
	{
		return $row;
	}
}
