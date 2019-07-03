<?php

/**
 * Companies list model class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Companies_ListView_Model extends Settings_Vtiger_ListView_Model
{
	public static $recordsCount;

	/**
	 * {@inheritdoc}
	 */
	public function getBasicLinks()
	{
		$basicLinks = parent::getBasicLinks();
		$basicLinks[] = [
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'LBL_REGISTER_CRM_ONLINE',
			'linkclass' => 'btn-light js-register-online ml-1',
			'linkicon' => 'fas fa-globe',
			'showLabel' => 1,
		];
		$basicLinks[] = [
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'LBL_REGISTER_CRM_SERIAL',
			'linkclass' => 'btn-light js-register-serial ml-1',
			'linkicon' => 'fas fa-receipt',
			'showLabel' => 1,
		];
		$basicLinks[] = [
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'LBL_REGISTER_CHECK',
			'linkclass' => 'btn-light js-register-check ml-1',
			'linkicon' => 'fas fa-check-square',
			'showLabel' => 1,
		];
		return $basicLinks;
	}
}
