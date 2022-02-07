<?php
/**
 * Action to get all icons.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function process(App\Request $request)
	{
		if ($request->getBoolean('skipImage')) {
			$icons = \App\Layout\Icon::getIcons();
		} else {
			$icons = \App\Layout\Icon::getAll();
		}
		$response = new Vtiger_Response();
		$response->setResult($icons);
		$response->emit();
	}
}
