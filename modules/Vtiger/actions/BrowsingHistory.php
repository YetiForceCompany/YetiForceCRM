<?php

/**
 * Browsing History Action Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Michał Lorencik <m.lorencik@yetiforce.com>
 */
class Vtiger_BrowsingHistory_Action extends Vtiger_BasicAjax_Action
{

	public function process(\App\Request $request)
	{
		Vtiger_BrowsingHistory_Helper::deleteHistory();
	}
}
