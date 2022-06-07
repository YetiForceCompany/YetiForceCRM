<?php

/**
 * Media module model file.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Media module model class.
 */
class Settings_Media_Module_Model extends Settings_Vtiger_Module_Model
{
	/** @var string Module name */
	public $name = 'Media';
	/** @var string Parent name */
	public $parent = 'Settings';
	/** @var \Settings_Picklist_Field_Model[] Fields model */
	protected $fields = [];

	/**
	 * Gets field instance by name.
	 *
	 * @param string $name
	 * @param bool   $edit
	 *
	 * @return \Vtiger_Field_Model
	 */
	public function getFieldInstanceByName(string $name)
	{
		if (!isset($this->fields[$name])) {
			$moduleName = $this->getName(true);
			$params = [];
			if ('image' === $name) {
				$params = [
					'name' => $name,
					'column' => $name,
					'label' => 'LBL_ICON',
					'uitype' => 69,
					'typeofdata' => 'V~O'
				];
			}
			$this->fields[$name] = \Vtiger_Field_Model::init($moduleName, $params, $name);
		}

		return $this->fields[$name];
	}
}
