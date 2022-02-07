<?php

/**
 * Action to save dashboard.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WidgetsManagement_Dashboard_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
	}

	public function save(App\Request $request)
	{
		Settings_WidgetsManagement_Module_Model::saveDashboard($request->getInteger('dashboardId'), $request->getByType('name', 'Text'));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	public function delete(App\Request $request)
	{
		$dashboardId = $request->getInteger('dashboardId');
		if ($dashboardId === Settings_WidgetsManagement_Module_Model::getDefaultDashboard()) {
			throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
		}
		Settings_WidgetsManagement_Module_Model::deleteDashboard($dashboardId);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
