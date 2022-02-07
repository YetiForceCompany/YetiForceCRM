<?php

/**
 * Settings login history module model file.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings login history module model  class.
 */
class Settings_LoginHistory_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $name = 'LoginHistory';

	/** {@inheritdoc} */
	public $baseTable = 'vtiger_loginhistory';

	/** {@inheritdoc} */
	public $baseIndex = 'login_id';

	/** @var string[] Columns to show on the list. */
	public $listFields = [
		'user_name' => 'LBL_USER_NAME',
		'status' => 'LBL_STATUS',
		'login_time' => 'LBL_LOGIN_TIME',
		'user_ip' => 'LBL_USER_IP_ADDRESS',
		'browser' => 'LBL_BROWSER',
		'logout_time' => 'LBL_LOGGED_OUT_TIME',
		'agent' => 'LBL_USER_AGENT',
	];

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string - url
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=LoginHistory&parent=Settings&view=List';
	}
}
