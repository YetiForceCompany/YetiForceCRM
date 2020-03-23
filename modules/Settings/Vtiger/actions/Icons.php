<?php
/**
 * Action to get all icons.
 *
 * @package Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

class Settings_Vtiger_Icons_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \ReflectionException
	 */
	public function process(\App\Request $request)
	{
		$response = new Vtiger_Response();
		$response->setResult(Settings_Vtiger_Icons_Model::getAll());
		$response->emit();
	}
}
