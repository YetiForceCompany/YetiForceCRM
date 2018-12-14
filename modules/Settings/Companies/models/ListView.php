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
	/**
	 * {@inheritdoc}
	 */
	public function getBasicLinks()
	{
		$basicLinks = parent::getBasicLinks();
		$basicLinks[] = [
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'LBL_REGISTER_CRM',
			'linkclass' => 'btn-light js-send',
			'linkicon' => 'far fa-registered',
			'showLabel' => 1,
		];
		return $basicLinks;
	}
}
