<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
/*
  Return Description
  ------------------------
  Info title: optional
  Info text: mandatory
  Type: 0 - notify
  Type: 1 - show quick create mondal
  Type: 2 - show notify info
  Type: 3 - modal window
 */

Class DataAccess_unique_account
{

	var $config = false;

	public function process($moduleName, $iD, $recordForm, $config)
	{
		$db = PearDatabase::getInstance();
		$params = [];
		$hierarchyAll = [];
		$save = true;
		$where = '';
		$hierarchyCheck = false;
		if ($iD != 0 && $iD != '' && !array_key_exists('vat_id', $recordForm)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($iD, $moduleName);
			$vatId = $recordModel->get('vat_id');
		} else {
			if (array_key_exists('vat_id', $recordForm))
				$vatId = $recordForm['vat_id'];
		}
		if ($iD != 0 && $iD != '' && !array_key_exists('accountname', $recordForm)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($iD, $moduleName);
			$accountName = $recordModel->get('accountname');
		} else {
			if (array_key_exists('accountname', $recordForm))
				$accountName = $recordForm['accountname'];
		}
		if ($vatId) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$hierarchyField = Vtiger_Field_Model::getInstance('account_id', $moduleModel);
			if ($hierarchyField->isActiveField()) {
				if (array_key_exists('account_id', $recordForm))
					$hierarchyValue = $recordForm['account_id'];
				elseif ($iD != 0 && $iD != '' && !array_key_exists('account_id', $recordForm)) {
					$recordModel = Vtiger_Record_Model::getInstanceById($iD, $moduleName);
					$hierarchyValue = $recordModel->get('account_id');
				}
				if ($hierarchyValue) {
					$hierarchyAll = $this->getHierarchy($hierarchyValue, $moduleName, $iD);
				} elseif ($iD) {
					$hierarchyAll = $this->getHierarchy($iD, $moduleName, $iD);
				}
			}
			$params[] = $vatId;
			$where .= ' vat_id = ?';
		} else {
			$params[] = $accountName;
			$where .= ' accountname = ?';
		}
		if ($iD != 0 && $iD != '') {
			$params[] = $iD;
			$where .= ' AND accountid <> ?';
		}

		if ($hierarchyAll && $vatId) {
			$hierarchyParams = array_merge($params, array_keys($hierarchyAll));
			$hierarchyQuery = 'SELECT accountid,accountname FROM vtiger_account WHERE ' . $where . ' AND accountid IN (' . $db->generateQuestionMarks($hierarchyAll) . ')';
			$result = $db->pquery($hierarchyQuery, $hierarchyParams);
			if ($db->getRowCount($result)) {
				$hierarchyCheck = true;
			}
			while ($row = $db->getRow($result)) {
				if ($row['accountname'] == $accountName) {
					$metaData = Vtiger_Functions::getCRMRecordMetadata($row['accountid']);
					$save = false;
					$fieldlabel .= '<li><a target="_blank" href="index.php?module=Accounts&view=Detail&record=' . $row['accountid'] . '"><strong>' . Vtiger_Functions::getCRMRecordLabel($row['accountid']) . '</strong></a> (' . Vtiger_Functions::getOwnerRecordLabel($metaData['smownerid']) . '),</li>';
				}
			}
		}
		if (!$hierarchyCheck) {
			$sql = "SELECT accountid FROM vtiger_account WHERE $where;";
			$result = $db->pquery($sql, $params);
			while ($id = $db->getSingleValue($result)) {
				$metaData = Vtiger_Functions::getCRMRecordMetadata($id);
				$save = false;
				$deletedLabel = $metaData['deleted'] ? ' - ' . vtranslate('LBL_RECORD_DELETED', 'DataAccess') : '';
				$fieldlabel .= '<li><a target="_blank" href="index.php?module=Accounts&view=Detail&record=' . $id . '"><strong>' . Vtiger_Functions::getCRMRecordLabel($id) . '</strong></a> (' . Vtiger_Functions::getOwnerRecordLabel($metaData['smownerid']) . ')' . $deletedLabel . ',</li>';
			}
		}

		if (!$save) {
			$permission = Users_Privileges_Model::isPermitted($moduleName, 'DuplicateRecord');
			$text = '<div class="marginLeft10">' . vtranslate('LBL_DUPLICATED_FOUND', 'DataAccess') . ': <br/ >' . trim($fieldlabel, ',') . '</div>';

			if ($permission) {
				$title = '<strong>' . vtranslate('LBL_DUPLICTAE_CREATION_CONFIRMATION', 'DataAccess') . '</strong>';
				if (!empty($iD)) {
					$text .= '<form class="form-horizontal"><div class="checkbox">
							<label>
								<input type="checkbox" name="cache"> ' . vtranslate('LBL_DONT_ASK_AGAIN', 'DataAccess') . '
							</label>
						</div></form>';
				}
				if ($recordForm['view'] == 'quick_edit') {
					$text = '<div class="alert alert-warning" role="alert">' . vtranslate('LBL_DUPLICTAE_QUICK_EDIT_CONFIRMATION', 'DataAccess') . '</div>' . $text;
				}
			}
			return Array(
				'save_record' => $save,
				'type' => 3,
				'info' => [
					'text' => $text,
					'title' => $title,
					'type' => $permission ? 1 : 0
				]
			);
		} else
			return Array('save_record' => true);
	}

	public function getConfig($id, $module, $baseModule)
	{
		return false;
	}

	public function getHierarchy($id, $moduleName, $recordId)
	{
		$hierarchyAll = [];
		$focus = CRMEntity::getInstance($moduleName);
		$hierarchy = $focus->getAccountHierarchy($id);
		unset($hierarchy['entries'][$recordId]);
		foreach ($hierarchy['entries'] as $hId => $value) {
			preg_match('/[.\s]+/', $value[0], $dashes);
			if ($dashes[0]) {
				$dash = explode($dashes[0], $value[0]);
				$value[0] = $dash[1];
			}
			$hierarchyAll[$hId] = strip_tags($value[0]);
		}
		return $hierarchyAll;
	}
}
