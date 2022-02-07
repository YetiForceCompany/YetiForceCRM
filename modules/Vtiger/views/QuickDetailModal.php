<?php

/**
 * Quick detail modal view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_QuickDetailModal_View extends \App\Controller\Modal
{
	/**
	 * Modal size.
	 *
	 * @var string
	 */
	public $modalSize = 'modal-lg modalRightSiteBar';
	/**
	 * Show modal header.
	 *
	 * @var bool
	 */
	public $showHeader = false;
	/**
	 * Show modal footer.
	 *
	 * @var bool
	 */
	public $showFooter = false;

	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$detailModel = Vtiger_DetailView_Model::getInstance($moduleName, $request->getInteger('record'));
		$recordModel = $detailModel->getRecord();
		$detailModel->getWidgets();
		$handlerClass = Vtiger_Loader::getComponentClassName('View', 'Detail', $moduleName);
		$detailView = new $handlerClass();
		$detailView->record = $detailModel;

		$widgets = [];
		foreach ($detailModel->widgets as $dw) {
			foreach ($dw as $widget) {
				if (!empty($widget['url'])) {
					parse_str($widget['url'], $output);
					$method = $output['mode'];
					$widgetRequest = new \App\Request($output, false);
					$widgetRequest->set('isReadOnly', true);
					if ($detailView->isMethodExposed($method)) {
						$label = '';
						if (!empty($widget['label'])) {
							$label = App\Language::translate($widget['label'], $moduleName);
						} elseif ('RelatedModule' === $widget['type']) {
							$relatedModule = App\Module::getModuleName($widget['data']['relatedmodule']);
							$label = App\Language::translate($relatedModule, $relatedModule);
						}
						$widgets[] = ['title' => $label, 'content' => $detailView->{$method}($widgetRequest), 'widgetData' => $widget];
					}
				} elseif ('Summary' === $widget['type']) {
					$request->set('isReadOnly', 'true');
					$widgets[] = [
						'content' => $detailView->showModuleSummaryView($request),
					];
				}
			}
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('LINKS', $this->getModalLinks($recordModel));
		$viewer->view('Modals/QuickDetailModal.tpl', $moduleName);
	}

	/**
	 * Links.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return Vtiger_Link_Model[]
	 */
	public function getModalLinks(Vtiger_Record_Model $recordModel)
	{
		$links = Vtiger_Link_Model::getAllByType($recordModel->getModule()->getId(), ['QUICK_DETAIL_MODAL_HEADER'])['QUICK_DETAIL_MODAL_HEADER'] ?? [];
		if ($recordModel->isEditable()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_BASIC',
				'linklabel' => 'LBL_QUICK_EDIT',
				'linkdata' => [
					'module' => $recordModel->getModuleName(),
					'record' => $recordModel->getId(),
				],
				'linkicon' => 'yfi yfi-quick-creation',
				'linkclass' => 'btn btn-outline-dark btn-sm js-quick-edit-modal',
				'linkhint' => 'LBL_QUICK_EDIT',
			]);
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_BASIC',
				'linklabel' => 'BTN_RECORD_EDIT',
				'linkurl' => $recordModel->getEditViewUrl(),
				'linkicon' => 'yfi yfi-full-editing-view',
				'linkclass' => 'btn btn-outline-dark btn-sm',
				'linkhint' => 'BTN_RECORD_EDIT',
				'linkhref' => true,
			]);
		}
		if ($recordModel->isViewable()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'LIST_VIEW_ACTIONS_RECORD_LEFT_SIDE',
				'linklabel' => 'LBL_SHOW_COMPLETE_DETAILS',
				'linkurl' => $recordModel->getFullDetailViewUrl(),
				'linkicon' => 'fas fa-th-list',
				'linkclass' => 'btn-sm btn-default',
				'linkhref' => true,
			]);
		}
		return $links;
	}
}
