<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

Class OSSMailView_preview_View extends Vtiger_Index_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}

	public function process(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$load = $request->get('noloadlibs');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);

		$from = $recordModel->get('from_email');
		$to = $recordModel->get('to_email');
		$to = explode(',', $to);
		$cc = $recordModel->get('cc_email');
		$bcc = $recordModel->get('bcc_email');
		$subject = $recordModel->get('subject');
		$owner = $recordModel->get('assigned_user_id');
		$sentTime = new DateTimeField($recordModel->get('createdtime'));
		$sent = $sentTime->getDisplayDateTimeValue();

		// pobierz załączniki
		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
				'Documents' ActivityType,vtiger_attachments.type  FileType,vtiger_crmentity.modifiedtime,
				vtiger_seattachmentsrel.attachmentsid attachmentsid, vtiger_notes.notesid crmid, vtiger_notes.notecontent description,vtiger_notes.*
				from vtiger_notes
				LEFT JOIN vtiger_notescf ON vtiger_notescf.notesid= vtiger_notes.notesid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid= vtiger_notes.notesid and vtiger_crmentity.deleted=0
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid =vtiger_notes.notesid
				LEFT JOIN vtiger_attachments ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid= vtiger_users.id
				LEFT JOIN vtiger_ossmailview_files ON vtiger_ossmailview_files.documentsid =vtiger_notes.notesid
				WHERE vtiger_ossmailview_files.ossmailviewid = ?";
		$params = array($record);
		$result = $db->pquery($query, $params, true);
		$num = $db->num_rows($result);

		$attachments = array();
		for ($i = 0; $i < $num; $i++) {
			$attachments[$i]['name'] = $db->query_result($result, $i, 'title');
			$attachments[$i]['file'] = $db->query_result($result, $i, 'filename');
			$attachments[$i]['id'] = $db->query_result($result, $i, 'crmid');
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('NOLOADLIBS', $load);
		$viewer->assign('FROM', $from);
		$viewer->assign('TO', $to);
		$viewer->assign('CC', $cc);
		$viewer->assign('BCC', $bcc);
		$viewer->assign('SUBJECT', $subject);
		$viewer->assign('URL', "index.php?module=$moduleName&view=mbody&record=$record");
		$viewer->assign('OWNER', $owner);
		$viewer->assign('SENT', $sent);
		$viewer->assign('ATTACHMENTS', $attachments);
		$viewer->assign('RECORD', $record);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('ISMODAL', $request->isAjax());
		$viewer->assign('SCRIPTS', $this->getModalScripts($request));
		$viewer->assign('SMODULENAME', $request->get('smodule'));
		$viewer->assign('SRECORD', $request->get('srecord'));
		$viewer->view('preview.tpl', 'OSSMailView');
	}

	public function getModalScripts(Vtiger_Request $request)
	{
		$scripts = [
			'~layouts/basic/modules/OSSMailView/resources/preview.js'
		];
		$modalScripts = $this->checkAndConvertJsScripts($scripts);
		return $modalScripts;
	}
}
