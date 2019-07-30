<?php
/**
 * Class for quick create ajax view.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */

/**
 * Class HelpDesk_QuickCreateAjax_View.
 */
class HelpDesk_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		parent::process($request);
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('CHECK_IF_RECORDS_HAS_TIME_CONTROL', \App\Config::module($moduleName, 'CHECK_IF_RECORDS_HAS_TIME_CONTROL'));
		$viewer->assign('CHECK_IF_RELATED_TICKETS_ARE_CLOSED', \App\Config::module($moduleName, 'CHECK_IF_RELATED_TICKETS_ARE_CLOSED'));
		$viewer->assign('CLOSE_TICKET_FOR_STATUS', \App\Json::encode(array_flip(\App\RecordStatus::getStates($moduleName, \App\RecordStatus::RECORD_STATE_CLOSED))));
	}
}
