<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private', false);

//Opensource fix for tracking email access count
chdir(dirname(__FILE__). '/../../../');

require_once 'includes/Loader.php';
require_once 'include/utils/utils.php';

vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport ('includes.runtime.Controller');

class Emails_TrackAccess_Action extends Vtiger_Action_Controller {

	public function process(Vtiger_Request $request) {
		if (vglobal('application_unique_key') !== $request->get('applicationKey')) {
			exit;
		}

		$parentId = $request->get('parentId');
		$recordId = $request->get('record');

		if ($parentId && $recordId) {
			$recordModel = Emails_Record_Model::getInstanceById($recordId);
			$recordModel->updateTrackDetails($parentId);
		}
	}

	function validateRequest(Vtiger_Request $request) {
		// This is a callback entry point file.
		return true;
	}
}

$track = new Emails_TrackAccess_Action();
$track->process(new Vtiger_Request($_REQUEST));
