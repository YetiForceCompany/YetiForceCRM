<?php

/**
 * The basic class to delete.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
