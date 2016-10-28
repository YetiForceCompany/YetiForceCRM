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

	public $config = false;

	public function process($moduleName, $ID, $recordForm, $config)
	{
		$db = PearDatabase::getInstance();
		$params = [];
		$hierarchyAll = [];
		$save = true;
		$where = '';
		$hierarchyCheck = false;
		if ($ID != 0 && $ID != '' && !array_key_exists('vat_id', $recordForm)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($ID, $moduleName);
			$vatId = $recordModel->get('vat_id');
		} else {
			if (array_key_exists('vat_id', $recordForm))
				$vatId = $recordForm['vat_id'];
		}
		if ($ID != 0 && $ID != '' && !array_key_exists('accountname', $recordForm)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($ID, $moduleName);
			$accountName = $recordModel->get('accountname');
		} else {
			if (array_key_exists('accountname', $recordForm))
				$accountName = $recordForm['accountname'];
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$hierarchyField = Vtiger_Field_Model::getInstance('account_id', $moduleModel);
		if ($hierarchyField->isActiveField()) {
			if (array_key_exists('account_id', $recordForm))
				$hierarchyValue = $recordForm['account_id'];
			elseif ($ID != 0 && $ID != '' && !array_key_exists('account_id', $recordForm)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($ID, $moduleName);
				$hierarchyValue = $recordModel->get('account_id');
			}
			if ($hierarchyValue) {
				$hierarchyAll = $this->getHierarchy($hierarchyValue, $moduleName, $ID);
			} elseif ($ID) {
				$hierarchyAll = $this->getHierarchy($ID, $moduleName, $ID);
			}
		}

		if ($vatId) {
			$params[] = $vatId;
			$where .= ' vat_id = ?';
		} else {
			$params[] = $accountName;
			$where .= ' accountname = ?';
		}
		if ($ID != 0 && $ID != '') {
			$params[] = $ID;
			$where .= ' AND accountid <> ?';
		}

		if ($hierarchyAll && $vatId) {
			$hierarchyParams = array_merge($params, array_keys($hierarchyAll));
			$hierarchyQuery = 'SELECT accountid,accountname FROM vtiger_account WHERE %s AND accountid IN (%s)';
			$hierarchyQuery = sprintf($hierarchyQuery, $where, $db->generateQuestionMarks($hierarchyAll));
			$result = $db->pquery($hierarchyQuery, $hierarchyParams);
			if ($db->getRowCount($result)) {
				$hierarchyCheck = true;
			}
			while ($row = $db->getRow($result)) {
				if ($row['accountname'] == $accountName) {
					$metaData = vtlib\Functions::getCRMRecordMetadata($row['accountid']);
					$save = false;
					$fieldlabel .= '<li><a target="_blank" href="index.php?module=Accounts&view=Detail&record=' . $row['accountid'] . '"><strong>' . vtlib\Functions::getCRMRecordLabel($row['accountid']) . '</strong></a> (' . vtlib\Functions::getOwnerRecordLabel($metaData['smownerid']) . '),</li>';
				}
			}
		}
		if (!$hierarchyCheck) {
			$sql = "SELECT accountid FROM vtiger_account WHERE $where;";
			$result = $db->pquery($sql, $params);
			while ($id = $db->getSingleValue($result)) {
				$metaData = vtlib\Functions::getCRMRecordMetadata($id);
				$save = false;
				$deletedLabel = $metaData['deleted'] ? ' - ' . vtranslate('LBL_RECORD_DELETED', 'DataAccess') : '';
				$fieldlabel .= '<li><a target="_blank" href="index.php?module=Accounts&view=Detail&record=' . $id . '"><strong>' . vtlib\Functions::getCRMRecordLabel($id) . '</strong></a> (' . vtlib\Functions::getOwnerRecordLabel($metaData['smownerid']) . ')' . $deletedLabel . ',</li>';
			}
		}
		if ($save === true && empty($recordForm['account_id']) === false && $ID > 0) {
			$recordModel = Vtiger_Record_Model::getInstanceById($ID, $moduleName);
			$hierarchyValueOld = $recordModel->get('account_id');
			if ($hierarchyValueOld != $recordForm['account_id']) {
				$hierarchyAll = $this->getHierarchy($recordForm['account_id'], $moduleName, '');
				if (array_key_exists($ID, $hierarchyAll) === true) {
					return [
						'save_record' => false,
						'type' => 0,
						'info' => [
							'title' => vtranslate('LBL_FAILED_TO_APPROVE_CHANGES', 'Settings:DataAccess'),
							'text' => vtranslate('LBL_PARENT_IS_CHILD', $moduleName),
							'type' => 'error'
						]
					];
				}
			}
		}
		if ($save === false) {
			$permission = Users_Privileges_Model::isPermitted($moduleName, 'DuplicateRecord');
			$text = '<div class="marginLeft10">' . vtranslate('LBL_DUPLICATED_FOUND', 'DataAccess') . ': <br/ >' . trim($fieldlabel, ',') . '</div>';

			if ($permission) {
				$title = '<strong>' . vtranslate('LBL_DUPLICTAE_CREATION_CONFIRMATION', 'DataAccess') . '</strong>';
				if (!empty($ID)) {
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
			return [
				'save_record' => $save,
				'type' => 3,
				'info' => [
					'text' => $text,
					'title' => $title,
					'type' => $permission ? 1 : 0
				]
			];
		} else {
			return ['save_record' => true];
		}
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
			preg_match('/[.\s]+/', $value[0]['data'], $dashes);
			if ($dashes[0]) {
				$dash = explode($dashes[0], $value[0]['data']);
				$value[0] = $dash[1];
			} else {
				$value[0] = $value[0]['data'];
			}
			$hierarchyAll[$hId] = strip_tags($value[0]);
		}
		return $hierarchyAll;
	}
}
