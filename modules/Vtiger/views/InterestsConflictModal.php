<?php
/**
 * Conflict of interest modal view file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Conflict of interest modal view class.
 */
class Vtiger_InterestsConflictModal_View extends \App\Controller\Modal
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public $modalSize = '';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** @var array|null Parent record id. */
	public $parent;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('confirmation');
		$this->exposeMethod('unlock');
		$this->exposeMethod('users');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true) || !\App\Privilege::isPermitted($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if ('users' === $request->getMode() && ($request->isEmpty('record') || !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record')))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->parent = \App\Components\InterestsConflict::getParent($request->getInteger('record'), $request->getModule());
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		$pageTitle = '';
		$this->modalIcon = 'yfi yfi-confirm-conflict';
		switch ($request->getMode()) {
			case 'unlock':
				$pageTitle = \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK', $moduleName);
				break;
			case 'confirmation':
				$this->lockExit = $this->parent ? true : false;
				$pageTitle = \App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRMATION', $moduleName);
				break;
			case 'users':
				$this->showFooter = true;
				$this->successBtn = '';
				$this->dangerBtn = 'BTN_CLOSE';
				$this->modalSize = 'modal-lg';
				$pageTitle = \App\Language::translate('LBL_INTERESTS_CONFLICT_USERS', $moduleName);
				break;
		}
		return $pageTitle;
	}

	/**
	 * Confirmation modal.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function confirmation(App\Request $request): void
	{
		$record = $request->getInteger('record');
		$viewer = $this->getViewer($request);
		$viewer->assign('SOURCE_RECORD', $record);
		if ($this->parent) {
			$viewer->assign('BASE_RECORD', $this->parent['id']);
			$viewer->assign('BASE_MODULE_NAME', $this->parent['moduleName']);
		}
		$viewer->view('Modals/InterestsConflictConfirmation.tpl', $request->getModule());
	}

	/**
	 * Unlock modal.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function unlock(App\Request $request): void
	{
		$record = $request->getInteger('record');
		$viewer = $this->getViewer($request);
		$viewer->assign('SOURCE_RECORD', $record);
		if ($this->parent) {
			$viewer->assign('BASE_RECORD', $this->parent['id']);
			$viewer->assign('BASE_MODULE_NAME', $this->parent['moduleName']);
		}
		$viewer->view('Modals/InterestsConflictUnlock.tpl', $request->getModule());
	}

	/**
	 * Users list modal.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function users(App\Request $request): void
	{
		$record = $request->getInteger('record');
		$viewer = $this->getViewer($request);
		$viewer->assign('SOURCE_RECORD', $record);
		if ($this->parent) {
			$viewer->assign('BASE_RECORD', $this->parent['id']);
			$viewer->assign('BASE_MODULE_NAME', $this->parent['moduleName']);
			$viewer->assign('USERS', \App\Components\InterestsConflict::getByRecord($this->parent['id']));
		}
		$viewer->view('Modals/InterestsConflictUsers.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/datatables.net/js/jquery.dataTables.js',
			'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
			'~libraries/datatables.net-responsive/js/dataTables.responsive.js',
			'~libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js',
		]));
	}

	/** {@inheritdoc} */
	public function getModalCss(App\Request $request)
	{
		return array_merge(parent::getModalCss($request), $this->checkAndConvertCssStyles([
			'~libraries/datatables.net-bs4/css/dataTables.bootstrap4.css',
			'~libraries/datatables.net-responsive-bs4/css/responsive.bootstrap4.css',
		]));
	}
}
