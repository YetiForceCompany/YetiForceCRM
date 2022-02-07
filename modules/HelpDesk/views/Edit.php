<?php

/**
 * Class to edit view.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class HelpDesk_Edit_View.
 */
class HelpDesk_Edit_View extends Vtiger_Edit_View
{
	/** {@inheritdoc} */
	public function loadJsConfig(App\Request $request)
	{
		parent::loadJsConfig($request);
		$moduleName = $request->getModule();
		foreach ([
			'checkIfRecordHasTimeControl' => ((bool) \App\Config::module($moduleName, 'CHECK_IF_RECORDS_HAS_TIME_CONTROL')) && \App\Module::isModuleActive('OSSTimeControl'),
			'checkIfRelatedTicketsAreClosed' => (bool) \App\Config::module($moduleName, 'CHECK_IF_RELATED_TICKETS_ARE_CLOSED'),
			'closeTicketForStatus' => array_flip(\App\RecordStatus::getStates($moduleName, \App\RecordStatus::RECORD_STATE_CLOSED))
		] as $key => $value) {
			\App\Config::setJsEnv($key, $value);
		}
	}
}
