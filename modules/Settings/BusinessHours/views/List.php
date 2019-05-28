<?php

/**
 * List View Class for Business Hours Settings.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class Settings_BusinessHours_List_View extends Settings_Vtiger_List_View
{
	/**
	 * {@inheritdoc}
	 */
	public function getBreadcrumbTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		return \App\Language::translate('LBL_BUSINESS_HOURS', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
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
		$module = $listViewModel->getModule();
		$viewer->assign('LISTVIEW_LINKS', $this->getListViewLinks($module));
		$viewer->assign('MODULE_MODEL', $module);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
	}

	/**
	 * Function to get Basic links.
	 *
	 * @param mixed $moduleModel
	 * @param mixed $listViewModel
	 *
	 * @return array of Basic links
	 */
	public function getBasicLinks($moduleModel)
	{
		$basicLinks = [];
		if ($moduleModel->hasCreatePermissions()) {
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_RECORD',
				'linkurl' => $moduleModel->getEditViewUrl(),
				'linkicon' => 'fas fa-plus',
				'linkclass' => 'btn-light addRecord',
				'showLabel' => 1,
				'modalView' => false,
			];
		}
		return $basicLinks;
	}

	/**
	 * Get list view links.
	 *
	 * @param mixed $moduleModel
	 *
	 * @return array
	 */
	public function getListViewLinks($moduleModel)
	{
		$links = ['LISTVIEWBASIC' => []];
		$basicLinks = $this->getBasicLinks($moduleModel);
		foreach ($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}
		return $links;
	}
}
