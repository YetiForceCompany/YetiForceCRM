<?php

/**
 * OSSPasswords save action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords_Save_Action extends Vtiger_Save_Action
{
	public function process(\App\Request $request)
	{
		$recordModel = $this->saveRecord($request);
		if ($request->getBoolean('relationOperation')) {
			$parentModuleName = $request->getByType('sourceModule', 2);
			$parentRecordId = $request->getInteger('sourceRecord');
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} elseif ($request->getBoolean('returnToList')) {
			$loadUrl = $recordModel->getModule()->getListViewUrl();
		} else {
			$loadUrl = $recordModel->getDetailViewUrl();
		}
		header("Location: $loadUrl");
	}

	/**
	 * Function to save record.
	 *
	 * @param \App\Request $request - values of the record
	 *
	 * @return Vtiger_Record_Model - record Model of saved record
	 */
	public function saveRecord(\App\Request $request)
	{
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
					$result = $adb->pquery($sql, [$recordModel->getId()], true);
					$properPassword = $adb->queryResult($result, 0, 'pass');
				} else {  // encryption mode is off
					$sql = 'SELECT `password` AS pass FROM `vtiger_osspasswords` WHERE `osspasswordsid` = ?;';
					$result = $adb->pquery($sql, [$recordModel->getId()], true);
					$properPassword = $adb->queryResult($result, 0, 'pass');
				}
			}
			$recordModel->set('password', $properPassword);
			$recordModel->save();

			// after save we check if encryption is active
			if ($config) {
				$sql = 'UPDATE `vtiger_osspasswords` SET `password` = AES_ENCRYPT(?,?) WHERE `osspasswordsid` = ?;';
				$result = $adb->pquery($sql, [$properPassword, $config['key'], $recordModel->getId()], true);
			}
		} else {
			$recordModel->save();
			if ($config) { // when encryption is on
				$sql = 'UPDATE `vtiger_osspasswords` SET `password` = AES_ENCRYPT(`password`, ?) WHERE `osspasswordsid` = ?;';
				$result = $adb->pquery($sql, [$config['key'], $recordModel->getId()], true);
			}
		}
		if ($request->getBoolean('relationOperation')) {
			$parentModuleName = $request->getByType('sourceModule', 2);
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->getInteger('sourceRecord');
			$relatedModule = $recordModel->getModule();
			$relatedRecordId = $recordModel->getId();

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}

		return $recordModel;
	}
}
