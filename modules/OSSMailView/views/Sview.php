<?php

/**
 * OSSMailView sview view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_Sview_View extends Vtiger_Index_View
{
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

		$recordPermission = \App\Privilege::isPermitted($moduleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		return true;
	}

	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}

	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$recordModel = OSSMailView_Record_Model::getInstanceById($record, $moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('NOLOADLIBS', $request->getBoolean('noloadlibs'));
		$viewer->assign('FROM', $recordModel->getDisplayValue('from_email'));
		$viewer->assign('TO', explode(',', $recordModel->getDisplayValue('to_email')));
		$viewer->assign('CC', $recordModel->getDisplayValue('cc_email'));
		$viewer->assign('BCC', $recordModel->getDisplayValue('bcc_email'));
		$viewer->assign('SUBJECT', $recordModel->getDisplayValue('subject'));
		$viewer->assign('OWNER', $recordModel->get('assigned_user_id'));
		$viewer->assign('SENT', $recordModel->get('createdtime'));
		$viewer->assign('ATTACHMENTS', $recordModel->getAttachments());
		$viewer->assign('RECORD', $record);
		if (\App\Utils::isHtml($recordModel->get('content'))) {
			$viewer->assign('CONTENT', $recordModel->getDisplayValue('content', false, false, 'full'));
		} else {
			$viewer->assign('CONTENT', nl2br(\App\Layout::truncateHtml(\App\Purifier::purify($recordModel->get('content')), 'full')));
		}
		$viewer->view('sview.tpl', 'OSSMailView');
	}
}
