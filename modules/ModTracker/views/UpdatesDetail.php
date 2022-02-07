<?php

/**
 * Updates detail.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
\Vtiger_Loader::includeOnce('~/modules/ModTracker/ModTracker.php');
/**
 * ModTracker_UpdatesDetail_View class.
 */
class ModTracker_UpdatesDetail_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-lg';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$selectedModule = $request->getByType('sourceModule', \App\Purifier::ALNUM);
		if (!\App\Privilege::isPermitted($selectedModule)) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$selectedModule = $request->getByType('sourceModule', \App\Purifier::ALNUM);
		return \App\Language::translate($request->getModule(), $request->getModule()) .
		": <span class='modCT_{$selectedModule} yfm-{$selectedModule} mr-1'></span>" . \App\Language::translate($selectedModule, $selectedModule);
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();

		$trackerAction = $request->getInteger('trackerAction', false);
		$dateRange = $request->getDateRange('dateRange');
		$owner = 'all' === $request->get('owner') ? null : $request->getInteger('owner');
		$historyOwner = 'all' === $request->get('historyOwner') ? null : $request->getInteger('historyOwner');
		$selectedModule = $request->getByType('sourceModule', \App\Purifier::ALNUM);

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $request->getInteger('page', 1));
		$pagingModel->set('limit', $request->getInteger('limit', 10));

		$updates = ModTracker_Updates_Helper::getUpdates($selectedModule, [$trackerAction], $dateRange, $owner, $historyOwner, $pagingModel);

		$dateRange = \App\Fields\Date::formatRangeToDisplay($dateRange);
		$viewer = $this->getViewer($request);
		$viewer->assign('UPDATES', $updates);
		$viewer->assign('URL', "index.php?module={$moduleName}&view=UpdatesDetail&sourceModule=$selectedModule&onlyBody=true&owner={$request->get('owner')}&historyOwner={$request->get('historyOwner')}&trackerAction={$trackerAction}&dateRange=" . implode(',', $dateRange));
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET_DATA', []);
		$viewer->view('Modals/UpdatesDetail.tpl', $moduleName);
	}
}
