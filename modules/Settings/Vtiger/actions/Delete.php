<?php

/**
 * The basic class to delete
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Vtiger_Delete_Action extends Settings_Vtiger_Basic_Action
{

	function __construct()
	{
		Settings_Vtiger_Tracker_Model::addBasic('delete');
		parent::__construct();
	}
}
