<?php

/**
 * Action to save dashboard
 * @package YetiForce.Action
 * @license licenses/License.html
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

	public function save(Vtiger_Request $request)
	{
		Settings_WidgetsManagement_Module_Model::saveDashboard($request->get('dashboardId'), $request->get('name'));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	public function delete(Vtiger_Request $request)
	{
		$dashboardId = $request->get('dashboardId');
		if($dashboardId === Settings_WidgetsManagement_Module_Model::getDefaultDashboard()) {
			throw new \Exception\AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
		}
		Settings_WidgetsManagement_Module_Model::deleteDashboard($dashboardId);
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
