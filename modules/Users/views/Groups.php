<?php

/**
 * Lider group view file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Lider group view class.
 */
class Users_Groups_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $successBtnIcon = 'far fa-save';

	/** {@inheritdoc} */
	public $successBtn = '';

	/** {@inheritdoc} */
	public $modalIcon = 'yfi-groups';

	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_GROUP_MEMBERS_CHANGE_VIEW';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$userModel = \App\User::getCurrentUserModel();

		if (!$userModel->get('leader') || !\App\Privilege::isPermitted($moduleName, 'LeaderCanManageGroupMembership')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$currentUserModel = \App\User::getCurrentUserModel();
		$groups = $currentUserModel->get('leader');
		$viewer = $this->getViewer($request);
		$viewer->assign('GROUPS', $groups);
		$viewer->assign('GROUP_SELECTED', current($groups));
		$viewer->view('Groups.tpl', $request->getModule());
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
