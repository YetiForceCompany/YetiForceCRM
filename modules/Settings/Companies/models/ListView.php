<?php

/**
 * Companies list model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_ListView_Model extends Settings_Vtiger_ListView_Model
{
	public static $recordsCount;

	/** {@inheritdoc} */
	public function getBasicLinks()
	{
		$basicLinks = parent::getBasicLinks();
		$basicLinks[] = [
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'LBL_REGISTER_CRM_ONLINE',
			'linkclass' => 'btn-light js-register-online ml-1',
			'linkicon' => 'yfi yfi-register-online',
			'showLabel' => 1,
		];
		$basicLinks[] = [
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'LBL_REGISTER_CRM_SERIAL',
			'linkclass' => 'btn-light js-register-serial ml-1',
			'linkicon' => 'yfi yfi-register-offline',
			'showLabel' => 1,
		];
		$basicLinks[] = [
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'LBL_REGISTER_CHECK',
			'linkclass' => 'btn-light js-register-check ml-1',
			'linkicon' => 'mdi mdi-progress-check',
			'showLabel' => 1,
		];
		return $basicLinks;
	}
}
