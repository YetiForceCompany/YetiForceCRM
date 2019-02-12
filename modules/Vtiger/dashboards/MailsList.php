<?php

/**
 * Vtiger MailsList dashboard class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_MailsList_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(\App\Request $request, $widget = null)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$user = $request->getByType('user', 2);
		$linkId = $request->getInteger('linkid');
		$data = $request->getAll();
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$viewer->assign('SCRIPTS', null);
		$viewer->assign('STYLES', null);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('USER', $user);
		$viewer->assign('ACCOUNTSLIST', OSSMail_Record_Model::getAccountsList(false, true));
		$viewer->assign('DATA', $data);
		if ($request->has('content')) {
			$viewer->view('dashboards/MailsListContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/MailsList.tpl', $moduleName);
		}
	}
}
