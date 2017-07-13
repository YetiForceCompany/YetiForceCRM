<?php

/**
 * Browsing History Action Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Michał Lorencik <m.lorencik@yetiforce.com>
 */
class Vtiger_BrowsingHistory_Action extends Vtiger_Action_Controller
{

	/**
	 * Checking permission
	 * @param \App\Request $request
	 * @throws \Exception\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!AppConfig::performance('BROWSING_HISTORY_WORKING')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Clear user browsing history process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		Vtiger_BrowsingHistory_Helper::deleteHistory();
	}
}
