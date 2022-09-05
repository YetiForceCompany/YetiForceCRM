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
		$this->exposeMethod('manageWidgets');
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

	public function manageWidgets(App\Request $request): void
	{
		$formData = $request->getMultiDimensionArray('form', [
			'dashboardBlockId' => App\Purifier::INTEGER,
			'sourceModule' => App\Purifier::ALNUM,
			'authorized' => App\Purifier::ALNUM,
			'widgetLinkId' => [App\Purifier::INTEGER],
			'actionOption' => App\Purifier::STANDARD,
			'dashboardId' => App\Purifier::INTEGER
		]);
		//duplikaty?
		$block = Settings_WidgetsManagement_Module_Model::getBlocksFromModule($formData['sourceModule'], $formData['authorized'], $formData['dashboardId']);
		$newBlockId = reset($block);
		if (!$newBlockId) {
			$widgetsManagementModel = new Settings_WidgetsManagement_Module_Model();
			$newBlockId = $widgetsManagementModel->addBlock(['authorized' => $formData['authorized'], 'dashboardId' => $formData['dashboardId']], $formData['sourceModule'], null)['id'];
		}
		$db = App\Db::getInstance();
		if ('copy' === $formData['actionOption']) {
			foreach ($formData['widgetLinkId'] as $id) {
				$moduleDashboardRow = (new App\Db\Query())->from('vtiger_module_dashboard')->where(['id' => $id])->one();
				if ($moduleDashboardRow) {
					$moduleDashboardRow['blockid'] = $newBlockId;
					unset($moduleDashboardRow['id']);
					$db->createCommand()->insert('vtiger_module_dashboard', $moduleDashboardRow)->execute();
				}
			}
		} else {
			$db->createCommand()->update('vtiger_module_dashboard', ['blockid' => $newBlockId], ['id' => $formData['widgetLinkId']])->execute();
		}
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
