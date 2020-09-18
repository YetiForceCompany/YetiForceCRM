<?php

/**
 * The basic class to delete.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Vtiger_Delete_Action extends \App\Controller\Action
{
	use \App\Controller\Traits\SettingsPermission;

	public function __construct()
	{
		Settings_Vtiger_Tracker_Model::setRecordId(\App\Request::_get('record'));
		Settings_Vtiger_Tracker_Model::addBasic('delete');
		parent::__construct();
	}
}
