<?php

/**
 * Advanced permission module model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_AdvancedPermission_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 'a_yf_adv_permission';
	public $baseIndex = 'id';
	public $listFields = ['name' => 'LBL_NAME', 'tabid' => 'LBL_MODULE', 'status' => 'LBL_STATUS', 'action' => 'LBL_ACTION', 'priority' => 'LBL_PRIORITY'];
	public $name = 'AdvancedPermission';
	public static $status = [0 => 'FL_ACTIVE', 1 => 'FL_INACTIVE'];
	public static $action = [0 => 'FL_UNLOCK_RECORD', 1 => 'FL_LOCK_RECORD'];
	public static $priority = [0 => 'Low', 1 => 'Medium', 2 => 'High'];

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string URL
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=AdvancedPermission&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for create view of the module.
	 *
	 * @return string URL
	 */
	public function getCreateRecordUrl()
	{
		return 'index.php?module=AdvancedPermission&parent=Settings&view=Edit';
	}
}
