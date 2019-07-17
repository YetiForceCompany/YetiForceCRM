<?php
/**
 * Help index view class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Settings_Help_Index_View extends Settings_Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('github');
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		$this->getViewer($request)->view('SettingsIndexHeader.tpl', $request->getModule(false));
	}

	/**
	 * Index.
	 *
	 * @param \App\Request $request
	 */
	public function index(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}

	/**
	 * Github mode.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function github(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = 'Settings:Github';
		$clientModel = Settings_Github_Client_Model::getInstance();
		$isAuthor = $request->getBoolean('author');
		$pageNumber = $request->getInteger('page', 1);
		$state = $request->isEmpty('state', true) ? 'open' : $request->getByType('state', 'Text');
		$issues = $clientModel->getAllIssues($pageNumber, $state, $isAuthor);
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('totalCount', Settings_Github_Issues_Model::$totalCount);
		$pageCount = $pagingModel->getPageCount();
		$startPaginFrom = $pagingModel->getStartPagingFrom();
		$viewer->assign('IS_AUTHOR', $isAuthor);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('ISSUES_STATE', $state);
		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', false);
		$viewer->assign('LISTVIEW_COUNT', Settings_Github_Issues_Model::$totalCount);
		$viewer->assign('START_PAGIN_FROM', $startPaginFrom);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('GITHUB_ISSUES', $issues);
		$viewer->assign('GITHUB_CLIENT_MODEL', $clientModel);
		$viewer->view('Github.tpl', $qualifiedModuleName);
	}
}
