<?php
/**
 * Conflict of interests index view file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\Components\View;

/**
 * Conflict of interests index view class.
 */
class InterestsConflict extends \App\Controller\View\Page
{
	/** {@inheritdoc} */
	public function checkPermission(\App\Request $request)
	{
		switch ($request->getMode()) {
			case 'unlock':
				if (!\in_array(\App\User::getCurrentUserId(), \Config\Components\InterestsConflict::$unlockUsersAccess) && !\App\User::getCurrentUserModel()->isAdmin()) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
				}
				$this->pageTitle = \App\Language::translate('LBL_UNLOCK_REQUESTS', 'Settings:InterestsConflict');
				break;
			case 'confirm':
				if (!\in_array(\App\User::getCurrentUserId(), \Config\Components\InterestsConflict::$unlockUsersAccess) && !\App\User::getCurrentUserModel()->isAdmin()) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
				}
$this->pageTitle = \App\Language::translate('LBL_CONFIRMATIONS', 'Settings:InterestsConflict');
				break;
			default:
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
				break;
		}
		return true;
	}

	/** {@inheritdoc} */
	public function getPageTitle(\App\Request $request)
	{
		return \App\Language::translate('LBL_CONFLICT_OF_INTEREST') . ' - ' . $this->pageTitle;
	}

	/** {@inheritdoc} */
	public function getBreadcrumbTitle(\App\Request $request)
	{
		return \App\Language::translate('LBL_CONFLICT_OF_INTEREST') . ' - ' . $this->pageTitle;
	}

	/** {@inheritdoc} */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODE', $request->getMode());
		$viewer->assign('USERS', \Users_Record_Model::getAll());
		$viewer->assign('DATE', implode(',', \App\Fields\Date::formatRangeToDisplay([date('Y-m-d', strtotime('-1 week')), date('Y-m-d')])));
		$viewer->assign('UNLOCK_STATUS_LIST', [
			\App\Components\InterestsConflict::UNLOCK_STATUS_NEW => \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_NEW'),
			\App\Components\InterestsConflict::UNLOCK_STATUS_ACCEPTED => \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_ACCEPTED'),
			\App\Components\InterestsConflict::UNLOCK_STATUS_REJECTED => \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_REJECTED'),
			\App\Components\InterestsConflict::UNLOCK_STATUS_CANCELED => \App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_CANCELED'),
		]);
		$viewer->assign('CONFIRM_STATUS_LIST', [
			\App\Components\InterestsConflict::CONF_STATUS_CONFLICT_NO => \App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_NO'),
			\App\Components\InterestsConflict::CONF_STATUS_CONFLICT_YES => \App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_YES'),
			\App\Components\InterestsConflict::CONF_STATUS_CANCELED => \App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_CANCELED'),
		]);
		$viewer->view('InterestsConflict.tpl', $request->getModule());
	}

	/** {@inheritdoc} */
	public function getFooterScripts(\App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/datatables.net/js/jquery.dataTables.js',
			'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
			'~libraries/datatables.net-responsive/js/dataTables.responsive.js',
			'~libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js',
			'components.InterestsConflict',
		]));
	}

	/** {@inheritdoc} */
	public function getHeaderCss(\App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			'~libraries/datatables.net-bs4/css/dataTables.bootstrap4.css',
			'~libraries/datatables.net-responsive-bs4/css/responsive.bootstrap4.css',
		]));
	}

	/** {@inheritdoc} */
	public function getJSLanguageStrings(\App\Request $request)
	{
		$translate = parent::getJSLanguageStrings($request);
		$translate['JS_UNLOCK_STATUS_NEW'] = \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_NEW');
		$translate['JS_UNLOCK_STATUS_ACCEPTED'] = \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_ACCEPTED');
		$translate['JS_UNLOCK_STATUS_REJECTED'] = \App\Language::translate('LBL_INTERESTS_CONFLICT_UNLOCK_STATUS_REJECTED');
		$translate['JS_UNLOCK_STATUS_CANCELED'] = \App\Language::translate('LBL_INTERESTS_CONFLICT_CONFIRM_CANCELED');
		$translate['JS_INTERESTS_CONFLICT_SET_CANCELED'] = \App\Language::translate('BTN_INTERESTS_CONFLICT_SET_CANCELED');
		$translate['BTN_UNLOCK_STATUS_ACTION_ACCEPT'] = \App\Language::translate('BTN_UNLOCK_STATUS_ACTION_ACCEPT');
		$translate['BTN_UNLOCK_STATUS_ACTION_REJECT'] = \App\Language::translate('BTN_UNLOCK_STATUS_ACTION_REJECT');
		return $translate;
	}
}
