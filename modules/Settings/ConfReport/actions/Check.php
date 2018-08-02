<?php

/**
 * ConfReport check actions model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_ConfReport_Check_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$newest = Settings_ConfReport_Module_Model::getNewestPhpVersion();
		$response = new Vtiger_Response();
		if ($newest) {
			$response->setResult([
				'text'=> \App\Language::translateArgs('LBL_LATEST_PHP_VERSIONS_ARE', 'Settings::ConfReport', implode(' , ', $newest)),
				'title' => \App\Language::translate('LBL_LATEST_PHP_TITLE', 'Settings::ConfReport')
			]);
		}
		$response->emit();
	}
}
