<?php

/**
 * Action to save dashboard
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WidgetsManagement_Dashboard_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
	}

	public function save(\App\Request $request)
	{
		Settings_WidgetsManagement_Module_Model::saveDashboard($request->get('dashboardId'), $request->get('name'));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	public function delete(\App\Request $request)
	{
		$dashboardId = $request->get('dashboardId');
		if ($dashboardId === Settings_WidgetsManagement_Module_Model::getDefaultDashboard()) {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
		Settings_WidgetsManagement_Module_Model::deleteDashboard($dashboardId);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
