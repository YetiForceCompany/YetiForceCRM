<?php

/**
 * Class to edit view.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Class HelpDesk_Edit_View.
 */
class HelpDesk_Edit_View extends Vtiger_Edit_View
{
	/**
	 * {@inheritdoc}
	 */
	public function loadJsConfig(App\Request $request)
	{
		parent::loadJsConfig($request);
		foreach ([
			'checkIfRecordHasTimeControl' => (bool) \App\Config::module('HelpDesk', 'CHECK_IF_RECORDS_HAS_TIME_CONTROL'),
			'checkIfRelatedTicketsAreClosed' => (bool) \App\Config::module('HelpDesk', 'CHECK_IF_RELATED_TICKETS_ARE_CLOSED')
		] as $key => $value) {
			\App\Config::setJsEnv($key, $value);
		}
	}
}
