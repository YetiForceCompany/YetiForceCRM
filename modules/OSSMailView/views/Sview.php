<?php

/**
 * OSSMailView sview view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailView_Sview_View extends Vtiger_Index_View
{

	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

		$recordPermission = \App\Privilege::isPermitted($moduleName, 'DetailView', $recordId);
		if (!$recordPermission) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
		return true;
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
	}

	public function process(\App\Request $request)
	{
		$db = PearDatabase::getInstance();
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$load = $request->get('noloadlibs');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$to = $recordModel->getForHtml('to_email');
		$to = explode(',', $to);
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
		$result = $db->pquery($query, [$record], true);
		$num = $db->numRows($result);

		$attachments = [];
		for ($i = 0; $i < $num; $i++) {
			$attachments[$i]['name'] = $db->queryResult($result, $i, 'title');
			$attachments[$i]['file'] = $db->queryResult($result, $i, 'filename');
			$attachments[$i]['id'] = $db->queryResult($result, $i, 'crmid');
		}

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
		$viewer->assign('ATTACHMENTS', $attachments);
		$viewer->assign('RECORD', $record);
		$viewer->view('sview.tpl', 'OSSMailView');
	}
}
