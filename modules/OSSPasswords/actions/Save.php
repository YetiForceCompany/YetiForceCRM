<?php

/**
 * OSSPasswords save action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSPasswords_Save_Action extends Vtiger_Save_Action
{

	public function process(\App\Request $request)
	{
		$recordModel = $this->saveRecord($request);
		if ($request->get('relationOperation')) {
			$parentModuleName = $request->getByType('sourceModule', 1);
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
	 * @param \App\Request $request - values of the record
	 * @return Vtiger_Record_Model - record Model of saved record
	 */
	public function saveRecord(\App\Request $request)
	{
		$recordId = $request->get('record');
		$recordModel = $this->getRecordModelFromRequest($request);
		$adb = PearDatabase::getInstance();
		// check if encryption is enabled
		$config = false;
		if (file_exists('modules/OSSPasswords/config.ini.php')) {
			$config = parse_ini_file('modules/OSSPasswords/config.ini.php');
		}

		//check if password was edited with hidden password
		$properPassword = $recordModel->get('password');
		// edit mode
		if (!$recordModel->isNew()) {
			if ($properPassword == '**********') { // hidden password sent in edit mode, get the correct one
				if ($config) { // when encryption is on
					$sql = sprintf("SELECT AES_DECRYPT(`password`, '%s') AS pass FROM `vtiger_osspasswords` WHERE `osspasswordsid` = ?;", $config['key']);
					$result = $adb->pquery($sql, [$recordId], true);
					$properPassword = $adb->queryResult($result, 0, 'pass');
				} else {  // encryption mode is off
					$sql = "SELECT `password` AS pass FROM `vtiger_osspasswords` WHERE `osspasswordsid` = ?;";
					$result = $adb->pquery($sql, array($recordId), true);
					$properPassword = $adb->queryResult($result, 0, 'pass');
				}
			}
			$recordModel->set('password', $properPassword);
			$recordModel->save();

			// after save we check if encryption is active
			if ($config) {
				$sql = "UPDATE `vtiger_osspasswords` SET `password` = AES_ENCRYPT(?,?) WHERE `osspasswordsid` = ?;";
				$result = $adb->pquery($sql, array($properPassword, $config['key'], $recordId), true);
			}
		} else {
			$recordModel->save();

			// if encryption mode is on we will encrypt the password
			$recordId = $recordModel->get('id');
			if ($config) { // when encryption is on
				$sql = "UPDATE `vtiger_osspasswords` SET `password` = AES_ENCRYPT(`password`, ?) WHERE `osspasswordsid` = ?;";
				$result = $adb->pquery($sql, array($config['key'], $recordId), true);
			}
		}

		if ($request->get('relationOperation')) {
			$parentModuleName = $request->getByType('sourceModule', 1);
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			$relatedRecordId = $recordModel->getId();

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}
		return $recordModel;
	}
}
