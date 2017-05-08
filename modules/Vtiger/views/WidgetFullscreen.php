<?php
/**
 * Widget fullscreen modal view class
 * @package YetiForce.Modal
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Widget fullscreen modal view class
 */
class Vtiger_WidgetFullscreen_View extends Vtiger_BasicModal_View
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
		return 'modal-blg';
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
		$mode = $request->getMode();
		$request->set('limit', 30);
		$request->set('isFullscreen', 'true');
		if ($detailView->isMethodExposed($mode)) {
			$content = $detailView->$mode($request);
		}
		$title = 'xx';
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CONTENT', $content);
		$viewer->assign('TITLE', $title);
		$viewer->view('WidgetFullscreen.tpl', $moduleName);
		$this->postProcess($request);
	}
}
