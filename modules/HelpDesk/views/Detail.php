<?php
/**
 * Class to detail view.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Class HelpDesk_Detail_View.
 */
class HelpDesk_Detail_View extends Vtiger_Detail_View
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
		$this->exposeMethod('showCharts');
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadJsConfig(App\Request $request)
	{
		parent::loadJsConfig($request);
		$moduleName = $request->getModule();
		foreach ([
			'checkIfRecordHasTimeControl' => ((bool) \App\Config::module($moduleName, 'CHECK_IF_RECORDS_HAS_TIME_CONTROL')) && \App\Module::isModuleActive('OSSTimeControl'),
			'checkIfRelatedTicketsAreClosed' => (bool) \App\Config::module($moduleName, 'CHECK_IF_RELATED_TICKETS_ARE_CLOSED'),
			'closeTicketForStatus' => \App\Json::encode(array_flip(\App\RecordStatus::getStates($moduleName, \App\RecordStatus::RECORD_STATE_CLOSED)))
		] as $key => $value) {
			\App\Config::setJsEnv($key, $value);
		}
	}

	public function showCharts(App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission('OSSTimeControl')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$data = ['show_chart' => false];
		$moduleModel = Vtiger_Module_Model::getInstance('OSSTimeControl');
		if ($moduleModel && $moduleModel->isActive()) {
			$data = $moduleModel->getTimeUsers($request->getInteger('record'), $moduleName);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->view('charts/ShowTimeHelpDesk.tpl', $moduleName);
	}
}
