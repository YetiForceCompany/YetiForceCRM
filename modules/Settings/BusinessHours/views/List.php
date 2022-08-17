<?php
/**
 * List View Class for Business Hours Settings.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_BusinessHours_List_View extends Settings_Vtiger_List_View
{
	/** {@inheritdoc} */
	public function getBreadcrumbTitle(App\Request $request)
	{
		return \App\Language::translate('LBL_BUSINESS_HOURS', $request->getModule(false));
	}

	/** {@inheritdoc} */
	public function initializeListViewContents(App\Request $request, Vtiger_Viewer $viewer)
	{
		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($request->getModule(false));
		$pagingModel = new Vtiger_Paging_Model();
		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		if (!$this->listViewEntries) {
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		if (!isset($this->listViewLinks)) {
			$this->listViewLinks = $listViewModel->getListViewLinks();
		}
		$module = $listViewModel->getModule();
		$viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);
		$viewer->assign('MODULE_MODEL', $module);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('COLUMN_NAME', '');
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', \count($this->listViewEntries));
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		if ($request->isJSON()) {
			$response = new Vtiger_Response();
			$listViewModel = Settings_Vtiger_ListView_Model::getInstance($request->getModule(false));
			$rows = [];
			foreach ($listViewModel->getListViewEntries(new Vtiger_Paging_Model()) as $recordModel) {
				$data = $recordModel->getData();
				foreach ($data as $name => $value) {
					$data[$name] = $recordModel->getDisplayValue($name);
				}
				$rows[] = $data;
			}
			$response->setResult(\App\Json::encode($rows));
			$response->emit();
		} else {
			$viewer = $this->getViewer($request);
			$this->initializeListViewContents($request, $viewer);
			$viewer->view('ListViewContents.tpl', $request->getModule(false));
		}
	}
}
