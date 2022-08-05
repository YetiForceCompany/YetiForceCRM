<?php

/**
 * The basic action file to save.
 *
 * @package Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * The basic action class to save.
 */
class Settings_Vtiger_Save_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		Settings_Vtiger_Tracker_Model::setRecordId(\App\Request::_get('record'));
		Settings_Vtiger_Tracker_Model::addBasic('save');
		parent::__construct();
	}
}
