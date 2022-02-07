<?php

/**
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_WidgetsManagement_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
		$this->exposeMethod('addBlock');
		$this->exposeMethod('removeBlock');
	}

	/**
	 * Add/Edit widget.
	 *
	 * @param App\Request $request
	 */
	public function save(App\Request $request)
	{
		if ($request->isEmpty('widgetId', true) && $linkData = \vtlib\Link::getLinkData($request->getInteger('linkId'))) {
			$recordModel = \Vtiger_Widget_Model::getInstanceFromValues($linkData);
		} else {
			$recordModel = \Vtiger_Widget_Model::getInstanceWithTemplateId($request->getInteger('widgetId'));
		}
		$recordModel->setDataFromRequest($request);
		$result = $recordModel->save();

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Delete widget.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function delete(App\Request $request)
	{
		$recordModel = \Vtiger_Widget_Model::getInstanceWithTemplateId($request->getInteger('widgetId'));
		$result = $recordModel->delete();
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function addBlock(App\Request $request)
	{
		$data = $request->getMultiDimensionArray('form', [
			'dashboardId' => 'Integer',
			'authorized' => 'Alnum'
		]);
		$moduleName = $request->getByType('sourceModule', 2);
		if (!\is_array($data) || !$data) {
			$result = ['success' => false, 'message' => \App\Language::translate('LBL_INVALID_DATA', $moduleName)];
		} else {
			$widgetsManagementModel = new Settings_WidgetsManagement_Module_Model();
			$result = $widgetsManagementModel->addBlock($data, $moduleName, null);
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function removeBlock(App\Request $request)
	{
		$data = $request->getMultiDimensionArray('form', [
			'blockid' => 'Integer',
		]);
		$moduleName = $request->getByType('sourceModule', 2);
		if (!\is_array($data) || !$data) {
			$result = ['success' => false, 'message' => \App\Language::translate('LBL_INVALID_DATA', $moduleName)];
		} else {
			$widgetsManagementModel = new Settings_WidgetsManagement_Module_Model();
			$result = $widgetsManagementModel->removeBlock($data);
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
