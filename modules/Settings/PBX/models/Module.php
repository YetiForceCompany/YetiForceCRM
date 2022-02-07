<?php

/**
 * PBX Module Model Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PBX_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Table name.
	 *
	 * @var string[]
	 */
	public $baseTable = 's_yf_pbx';

	/**
	 * Table name.
	 *
	 * @var string[]
	 */
	public $baseIndex = 'pbxid';

	/**
	 * Module Name.
	 *
	 * @var string
	 */
	public $name = 'PBX';

	/**
	 * List of fields displayed in list view.
	 *
	 * @var string[]
	 */
	public $listFields = ['name' => 'LBL_NAME', 'type' => 'LBL_TYPE', 'default' => 'LBL_DEFAULT'];
}
