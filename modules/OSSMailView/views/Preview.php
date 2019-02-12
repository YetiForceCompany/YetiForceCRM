<?php

/**
 * OSSMailView preview view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_Preview_View extends Vtiger_Index_View
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
		$from = $recordModel->getDisplayValue('from_email');
		$to = $recordModel->getDisplayValue('to_email');
		$to = explode(',', $to);
		$cc = $recordModel->getDisplayValue('cc_email');
		$bcc = $recordModel->getDisplayValue('bcc_email');
		$subject = $recordModel->getDisplayValue('subject');
		$owner = $recordModel->getDisplayValue('assigned_user_id');
		$sent = $recordModel->getDisplayValue('createdtime');
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('NOLOADLIBS', $load);
		$viewer->assign('FROM', $from);
		$viewer->assign('TO', $to);
		$viewer->assign('CC', $cc);
		$viewer->assign('BCC', $bcc);
		$viewer->assign('SUBJECT', $subject);
		$viewer->assign('URL', "index.php?module=$moduleName&view=Mbody&record=$record");
		$viewer->assign('OWNER', $owner);
		$viewer->assign('SENT', $sent);
		$viewer->assign('ATTACHMENTS', $recordModel->getAttachments());
		$viewer->assign('RECORD', $record);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('ISMODAL', $request->isAjax());
		$viewer->assign('SCRIPTS', $this->getModalScripts($request));
		$viewer->assign('SMODULENAME', $request->getByType('smodule'));
		$viewer->assign('SRECORD', $request->getInteger('srecord'));
		$viewer->view('preview.tpl', 'OSSMailView');
	}

	public function getModalScripts(\App\Request $request)
	{
		return $this->checkAndConvertJsScripts([
			'~layouts/basic/modules/OSSMailView/resources/preview.js',
		]);
	}
}
