<?php

/**
 * 
 * @package YetiForce.Models
 * @license licenses/License.html
 * @author Mriusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_LoginHistory_Module_Model extends Settings_Vtiger_Module_Model
{

	var $baseTable = 'vtiger_loginhistory';
	var $baseIndex = 'login_id';
	var $listFields = Array(
		'user_name' => 'LBL_USER_NAME',
		'user_ip' => 'LBL_USER_IP_ADDRESS',
		'login_time' => 'LBL_LOGIN_TIME',
		'logout_time' => 'LBL_LOGGED_OUT_TIME',
		'status' => 'LBL_STATUS'
	);
	var $name = 'LoginHistory';

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=LoginHistory&parent=Settings&view=List';
	}
}
