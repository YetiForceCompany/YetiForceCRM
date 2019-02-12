<?php

/**
 * OSSMailView sview view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_Sview_View extends Vtiger_Index_View
{
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

		$recordPermission = \App\Privilege::isPermitted($moduleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		return true;
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$load = $request->get('noloadlibs');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$to = $recordModel->getForHtml('to_email');
		$to = explode(',', $to);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('NOLOADLIBS', $load);
		$viewer->assign('FROM', $recordModel->getForHtml('from_email'));
		$viewer->assign('TO', $to);
		$viewer->assign('CC', $recordModel->getForHtml('cc_email'));
		$viewer->assign('BCC', $recordModel->getForHtml('bcc_email'));
		$viewer->assign('SUBJECT', $recordModel->getForHtml('subject'));
		$viewer->assign('URL', "index.php?module=$moduleName&view=Mbody&record=$record");
		$viewer->assign('OWNER', $recordModel->get('assigned_user_id'));
		$viewer->assign('SENT', $recordModel->get('createdtime'));
		$viewer->assign('ATTACHMENTS', $recordModel->getAttachments());
		$viewer->assign('RECORD', $record);
		$viewer->view('sview.tpl', 'OSSMailView');
	}
}
