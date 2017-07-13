<?php

/**
 * Vtiger MailsList dashboard class
 * @package YetiForce.Dashboard
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Vtiger_MailsList_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(\App\Request $request, $widget = NULL)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$user = $request->get('user');
		$linkId = $request->get('linkid');
		$data = $request->getAll();
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('USER', $user);
		$viewer->assign('ACCOUNTSLIST', OSSMail_Record_Model::getAccountsList(false, true));
		$viewer->assign('DATA', $data);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/MailsListContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/MailsList.tpl', $moduleName);
		}
	}
}
