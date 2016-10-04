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

class OSSPasswords_Save_Action extends Vtiger_Save_Action
{

	public function process(Vtiger_Request $request)
	{
		$recordModel = $this->saveRecord($request);
		if ($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentRecordId = $request->get('sourceRecord');
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} else if ($request->get('returnToList')) {
			$loadUrl = $recordModel->getModule()->getListViewUrl();
		} else {
			$loadUrl = $recordModel->getDetailViewUrl();
		}
		header("Location: $loadUrl");
	}

	/**
	 * Function to save record
	 * @param <Vtiger_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request)
	{
		$recordId = $request->get('record');
		$recordModel = $this->getRecordModelFromRequest($request);
		$mode = $recordModel->get('mode');

		$adb = PearDatabase::getInstance();

		// check if encryption is enabled
		$config = false;
		if (file_exists('modules/OSSPasswords/config.ini.php')) {
			$config = parse_ini_file('modules/OSSPasswords/config.ini.php');
		}

		//check if password was edited with hidden password
		$properPassword = $recordModel->get('password');
		// edit mode
		if ($recordId != '' && $mode == 'edit') {
			if ($properPassword == '**********') { // hidden password sent in edit mode, get the correct one
				if ($config) { // when encryption is on
					$sql = sprintf("SELECT AES_DECRYPT(`password`, '%s') AS pass FROM `vtiger_osspasswords` WHERE `osspasswordsid` = ?;", $config['key']);
					$result = $adb->pquery($sql, [$recordId], true);
					$properPassword = $adb->query_result($result, 0, 'pass');
				} else {  // encryption mode is off
					$sql = "SELECT `password` AS pass FROM `vtiger_osspasswords` WHERE `osspasswordsid` = ?;";
					$result = $adb->pquery($sql, array($recordId), true);
					$properPassword = $adb->query_result($result, 0, 'pass');
				}
			}
			$recordModel->set('password', $properPassword);
			$recordModel->save();

			// after save we check if encryption is active
			if ($config) {
				$sql = "UPDATE `vtiger_osspasswords` SET `password` = AES_ENCRYPT(?,?) WHERE `osspasswordsid` = ?;";
				$result = $adb->pquery($sql, array($properPassword, $config['key'], $recordId), true);
			}
		} else if ($recordId == '' && $mode == '') {
			$recordModel->save();

			// if encryption mode is on we will encrypt the password
			$recordId = $recordModel->get('id');
			if ($config) { // when encryption is on
				$sql = "UPDATE `vtiger_osspasswords` SET `password` = AES_ENCRYPT(`password`, ?) WHERE `osspasswordsid` = ?;";
				$result = $adb->pquery($sql, array($config['key'], $recordId), true);
			}
		}

		if ($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			$relatedRecordId = $recordModel->getId();

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}
		return $recordModel;
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Vtiger_Request $request)
	{

		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if (!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('mode', '');
		}

		$fieldModelList = $moduleModel->getFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			if (!$fieldModel->isEditEnabled()) {
				continue;
			}
			$fieldValue = $request->get($fieldName, null);
			$fieldDataType = $fieldModel->getFieldDataType();
			if ($fieldDataType == 'time') {
				$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
			}
			if ($fieldValue !== null) {
				if (!is_array($fieldValue)) {
					$fieldValue = trim($fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			}
		}
		return $recordModel;
	}
}
