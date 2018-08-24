<?php

/**
 * Log module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 */
class Settings_Log_Module_Model extends Settings_Vtiger_Module_Model
{
	public $name = 'Log';

	/**
	 * Array of table header.
	 *
	 * @var array
	 */
	public static $tableHeaders = [
		'access_for_admin' => ['id', 'username', 'date', 'ip', 'module', 'url', 'agent', 'request', 'referer'],
		'access_for_api' => ['id', 'username', 'date', 'ip', 'url', 'agent', 'request'],
		'access_for_user' => ['id', 'username', 'date', 'ip', 'module', 'url', 'agent', 'request', 'referer'],
		'access_to_record' => ['id', 'username', 'date', 'ip', 'record', 'module', 'url', 'agent', 'request', 'referer'],
		'csrf' => ['id', 'username', 'date', 'ip', 'referer', 'url', 'agent'],
	];

	/**
	 * Function to get the url for default view of the module.
	 *
	 * @return string - url
	 */
	public function getDefaultUrl()
	{
		return 'index.php?module=Log&parent=Settings&view=List';
	}
}
