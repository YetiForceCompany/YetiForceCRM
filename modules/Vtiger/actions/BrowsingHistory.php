<?php

/**
 * Browsing History Action Class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 */
class Vtiger_BrowsingHistory_Action extends \App\Controller\Action
{
	/**
	 * Checking permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		if (!App\Config::performance('BROWSING_HISTORY_WORKING')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Clear user browsing history process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		Vtiger_BrowsingHistory_Helper::deleteHistory();
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
