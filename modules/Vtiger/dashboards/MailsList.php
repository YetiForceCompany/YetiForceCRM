<?php

/**
 * Vtiger MailsList dashboard class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_MailsList_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request, $widget = null)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$user = $request->isEmpty('mailAccount') ? null : $request->getInteger('mailAccount');
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUser->getId());
		$viewer->assign('SCRIPTS', null);
		$viewer->assign('STYLES', null);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('USER', $user);
		if ($request->has('content')) {
			$viewer->view('dashboards/MailsListContents.tpl', $moduleName);
		} else {
			$viewer->assign('ACCOUNTSLIST', OSSMail_Record_Model::getAccountsList(false, true));
			$viewer->view('dashboards/MailsList.tpl', $moduleName);
		}
	}
}
