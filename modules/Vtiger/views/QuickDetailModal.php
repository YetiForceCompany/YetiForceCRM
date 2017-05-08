<?php

/**
 * Quick detail modal view class
 * @package YetiForce.Modal
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_QuickDetailModal_View extends Vtiger_BasicModal_View
{

	/**
	 * Checking permissions
	 * @param \App\Request $request
	 * @throws \Exception\AppException
	 * @throws \Exception\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$recordId = $request->get('record');
		if (!is_numeric($recordId)) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		$recordPermission = Users_Privileges_Model::isPermitted($request->getModule(), 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function getSize(\App\Request $request)
	{
		return 'modalRightSiteBar';
	}

	public function process(\App\Request $request)
	{
		$this->preProcess($request);
		$moduleName = $request->getModule();
		$detailModel = Vtiger_DetailView_Model::getInstance($moduleName, $request->get('record'));
		$recordModel = $detailModel->getRecord();
		$detailModel->getWidgets();
		$handlerClass = Vtiger_Loader::getComponentClassName('View', 'Detail', $moduleName);
		$detailView = new $handlerClass();

		$widgets = [];
		foreach ($detailModel->widgets as $dw) {
			foreach ($dw as $widget) {
				if (!empty($widget['url'])) {
					parse_str($widget['url'], $output);
					$method = $output['mode'];
					$widgetRequest = new \App\Request($output);
					$widgetRequest->set('isReadOnly', 'true');
					if ($detailView->isMethodExposed($method)) {
						$label = '';
						if (!empty($widget['label'])) {
							$label = App\Language::translate($widget['label'], $moduleName);
						} elseif ($widget['type'] === 'RelatedModule') {
							$relatedModule = App\Module::getModuleName($widget['data']['relatedmodule']);
							$label = App\Language::translate($relatedModule, $relatedModule);
						}
						$widgets[] = ['title' => $label, 'content' => $detailView->$method($widgetRequest)];
					}
				} elseif ($widget['type'] === 'Summary') {
					$request->set('isReadOnly', 'true');
					$widgets[] = [
						'content' => $detailView->showModuleSummaryView($request)
					];
				}
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->view('QuickDetailModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
