<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_NoteBook_Action extends Vtiger_Action_Controller
{

	public function __construct()
	{
		$this->exposeMethod('NoteBookCreate');
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();

		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	public function NoteBookCreate(Vtiger_Request $request)
	{
		$adb = PearDatabase::getInstance();
		$userModel = Users_Record_Model::getCurrentUserModel();
		$linkId = $request->get('linkId');
		$noteBookName = $request->get('notePadName');
		$noteBookContent = $request->get('notePadContent');
		$blockid = $request->get('blockid');
		$isdefault = $request->get('isdefault');
		$width = $request->get('width');
		$height = $request->get('height');

		$date_var = date("Y-m-d H:i:s");
		$date = $adb->formatDate($date_var, true);

		$dataValue = [];
		$dataValue['contents'] = $noteBookContent;
		$dataValue['lastSavedOn'] = $date;

		$data = \includes\utils\Json::encode((object) $dataValue);
		$size = \includes\utils\Json::encode(array('width' => $width, 'height' => $height));
		$query = "INSERT INTO vtiger_module_dashboard(`linkid`, `blockid`, `filterid`, `title`, `data`, `isdefault`, `size`) VALUES(?,?,?,?,?,?,?)";
		$params = array($linkId, $blockid, 0, $noteBookName, $data, $isdefault, $size);
		$adb->pquery($query, $params);
		$id = $adb->getLastInsertID();

		$result = [];
		$result['success'] = TRUE;
		$result['widgetId'] = $id;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
