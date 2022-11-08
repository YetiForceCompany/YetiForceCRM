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
		$messages = [];
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), \App\User::getCurrentUserId());
		$limit = (int) $widget->get('limit') ?: 5;
		$users = OSSMail_Autologin_Model::getAutologinUsers();
		$user = $request->isEmpty('mailAccount') ? key($users) : $request->getInteger('mailAccount');

		if ($user && isset($users[$user])) {
			$messages = \App\Mail\Account::getInstanceById($user)->openImap()->getLastMessages($limit);
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('SCRIPTS', null);
		$viewer->assign('STYLES', null);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('USER', $user);
		$viewer->assign('MAILS', $messages);
		if ($request->has('content')) {
			$viewer->view('dashboards/MailsListContents.tpl', $moduleName);
		} else {
			$viewer->assign('ACCOUNTSLIST', $users);
			$viewer->view('dashboards/MailsList.tpl', $moduleName);
		}
	}
}
