<?php
/**
 * Widget fullscreen modal view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Widget fullscreen modal view class.
 */
class Vtiger_WidgetFullscreen_View extends Vtiger_BasicModal_View
{
	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function getSize(App\Request $request)
	{
		return 'modal-blg';
	}

	public function process(App\Request $request)
	{
		$this->preProcess($request);
		$moduleName = $request->getModule();
		$detailModel = Vtiger_DetailView_Model::getInstance($moduleName, $request->getInteger('record'));
		$recordModel = $detailModel->getRecord();
		$detailModel->getWidgets();
		$handlerClass = Vtiger_Loader::getComponentClassName('View', 'Detail', $moduleName);
		$detailView = new $handlerClass();
		$detailView->record = $detailModel;
		$mode = $request->getMode();
		$request->set('limit', 30);
		$request->set('isFullscreen', 'true');
		if ($detailView->isMethodExposed($mode)) {
			$content = $detailView->{$mode}($request);
		}
		$title = '';
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CONTENT', $content);
		$viewer->assign('TITLE', $title);
		$viewer->view('WidgetFullscreen.tpl', $moduleName);
		$this->postProcess($request);
	}
}
