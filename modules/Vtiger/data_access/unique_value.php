<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
/*
  Return Description
  ------------------------
  Info type: error, info, success
  Info title: optional
  Info text: mandatory
  Type: 0 - notify
  Type: 1 - show quick create mondal
  Type: 2 - show notify info
 */

Class DataAccess_unique_value
{

	public $config = true;

	public function process($moduleName, $ID, $record_form, $config)
	{
		$db = PearDatabase::getInstance();
		$moduleNameID = vtlib\Functions::getModuleId($moduleName);
		$fieldlabel = $sql_ext = '';
		$save_record1 = true;
		$save_record2 = true;
		$save_record = true;
		$type = 0;
		$typeInfo = 'info';
		$info = false;
		$searchTrash = ['query' => '', 'params' => ''];
		if ($ID != 0 && $ID != '' && !array_key_exists($config['what1'], $record_form)) {
			$Record_Model = Vtiger_Record_Model::getInstanceById($ID, $moduleName);
			$value1 = $Record_Model->get($config['what1']);
		} else {
			if (array_key_exists($config['what1'], $record_form))
				$value1 = $record_form[$config['what1']];
		}

		if ($ID != 0 && $ID != '' && !array_key_exists($config['what2'], $record_form)) {
			$Record_Model = Vtiger_Record_Model::getInstanceById($ID, $moduleName);
			$value2 = $Record_Model->get($config['what2']);
		} else {
			if (array_key_exists($config['what2'], $record_form))
				$value2 = $record_form[$config['what2']];
		}

		if (!is_array($config['where1']))
			$wheres1[] = $config['where1'];
		else
			$wheres1 = $config['where1'];
		if (!is_array($config['where2']))
			$wheres2[] = $config['where2'];
		else
			$wheres2 = $config['where2'];
		if ($value1 != '') {
			foreach ($wheres1 as $where) {
				$where = explode('=', $where);
				$DestModuleName = vtlib\Functions::getModuleName($where[2]);
				$ModuleInstance = CRMEntity::getInstance($DestModuleName);
				$tab_name_index = $ModuleInstance->tab_name_index;
				$index = $tab_name_index[$where[0]];
				$sql_param = array($value1);
				$sql_ext = '';
				$spacialCondition = '';
				$sqlSpecial = '';
				if ($moduleNameID == $where[2] && $ID != 0 && $ID != '') {
					$sql_param[] = $ID;
					$sql_ext = 'AND ' . $index . ' <> ?';
				}
				if (empty($config['searchTrash'])) {
					if ($where[0] != 'vtiger_crmentity')
						$searchTrash = ['query' => " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $where[0].$index ", 'params' => ' AND vtiger_crmentity.deleted = 0 '];
					else {
						$searchTrash = ['query' => '', 'params' => ' vtiger_crmentity.delete = 0 '];
					}
				}
				if ($DestModuleName == 'Leads') {
					$spacialCondition = ' AND converted = 0';
					if ('vtiger_crmentity' == $where[0]) {
						$sqlSpecial = 'INNER JOIN vtiger_leaddetails ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid ';
					}
				}
				$result = $db->pquery("SELECT $index FROM {$where[0]} $sqlSpecial " . $searchTrash['query'] . " WHERE {$where[1]} = ? $sql_ext $spacialCondition " . $searchTrash['params'] . ";", $sql_param, true);
				$num = $db->num_rows($result);
				for ($i = 0; $i < $num; $i++) {
					$id = $db->query_result_raw($result, $i, $index);
					$metadata = vtlib\Functions::getCRMRecordMetadata($id);
					if ($metadata['setype'] == $DestModuleName) {
						$save_record1 = false;
						$deletedLabel = $metadata['deleted'] ? ' - ' . vtranslate('LBL_RECORD_DELETED', 'DataAccess') : '';
						$fieldlabel .= '<li><a target="_blank" href="index.php?module=' . $DestModuleName . '&view=Detail&record=' . $id . '"><strong>' . vtlib\Functions::getCRMRecordLabel($id) . '</strong></a> (' . vtlib\Functions::getOwnerRecordLabel($metadata['smownerid']) . ')' . $deletedLabel . ',</li>';
					}
				}
			}
		}
		if ($value2 != '') {
			foreach ($wheres2 as $where) {
				$where = explode('=', $where);
				$DestModuleName = vtlib\Functions::getModuleName($where[2]);
				$ModuleInstance = CRMEntity::getInstance($DestModuleName);
				$tab_name_index = $ModuleInstance->tab_name_index;
				$index = $tab_name_index[$where[0]];
				$sql_param = array($value2);
				$sql_ext = '';
				$spacialCondition = '';
				$sqlSpecial = '';
				if ($moduleNameID == $where[2] && $ID != 0 && $ID != '') {
					$sql_param[] = $ID;
					$sql_ext = 'AND ' . $index . ' <> ?';
				}
				if ($DestModuleName == 'Leads') {
					$spacialCondition = ' && `converted` = 0';
					if ('vtiger_crmentity' == $where[0]) {
						$sqlSpecial = 'INNER JOIN vtiger_leaddetails ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid ';
					}
				}
				if (empty($config['searchTrash'])) {
					if ($where[0] != 'vtiger_crmentity')
						$searchTrash = ['query' => " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $where[0].$index ", 'params' => ' && vtiger_crmentity.deleted = 0 '];
					else {
						$searchTrash = ['query' => '', 'params' => ' vtiger_crmentity.delete = 0 '];
					}
				}
				$result = $db->pquery("SELECT $index FROM {$where[0]} " . $searchTrash['query'] . " WHERE {$where[1]} = ? $sql_ext " . $searchTrash['params'] . ";", $sql_param, true);
				$num = $db->num_rows($result);
				for ($i = 0; $i < $num; $i++) {
					$id = $db->query_result_raw($result, $i, $index);
					$metadata = vtlib\Functions::getCRMRecordMetadata($id);
					if ($metadata['setype'] == $DestModuleName) {
						$save_record2 = false;
						$deletedLabel = $metadata['deleted'] ? ' - ' . vtranslate('LBL_RECORD_DELETED', 'DataAccess') : '';
						$fieldlabel .= '<li><a target="_blank" href="index.php?module=' . $DestModuleName . '&view=Detail&record=' . $id . '"><strong>' . vtlib\Functions::getCRMRecordLabel($id) . '</strong></a> (' . vtlib\Functions::getOwnerRecordLabel($metadata['smownerid']) . ')' . $deletedLabel . ',</li>';
					}
				}
			}
		}
		if ($config['locksave'] == 0) {
			$info = $config['info0'];
			$type = 2;
			$save_record = (!$save_record1 || !$save_record2) ? false : true;
		} elseif (!$save_record1 && !$save_record2) {
			$typeInfo = 'error';
			$save_record = false;
			$info = $config['info2'];
		} elseif (!$save_record1 || !$save_record2) {
			$typeInfo = 'error';
			$save_record = false;
			$info = $config['info1'];
		}
		if ($config['locksave'] == 3 && !$save_record) {
			$type = $config['locksave'];
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
				if ($record_form['view'] == 'quick_edit') {
					$text = '<div class="alert alert-warning" role="alert">' . vtranslate('LBL_DUPLICTAE_QUICK_EDIT_CONFIRMATION', 'DataAccess') . '</div>' . $text;
				}
			}
			$info = ['text' => $text,
				'title' => $title,
				'type' => $permission ? 1 : 0];
		}

		if (!$save_record)
			return Array(
				'save_record' => $save_record,
				'type' => $type,
				'info' => is_array($info) ? $info : [
					'text' => vtranslate($info, 'DataAccess') . ' <br/ >' . trim($fieldlabel, ','),
					'ntype' => $typeInfo,
					'hide' => false,
					]
			);
		else
			return Array('save_record' => true);
	}

	public function getConfig($id, $module, $baseModule)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT * FROM vtiger_field LEFT JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid  WHERE vtiger_field.presence <> '1' && vtiger_field.displaytype IN ('1','10') ORDER BY name", [], true);
		$fields = [];
		$ModuleFields = [];
		while ($row = $db->fetch_array($result)) {
			array_push($fields, array($row['fieldlabel'], $row['tablename'], $row['columnname'], $row['name'], $row['tabid'], $row['fieldname']));
			if ($row['name'] == $baseModule) {
				array_push($ModuleFields, array($row['name'], $row['fieldname'], $row['fieldlabel']));
			}
		}
		return Array('fields' => $fields, 'fields_mod' => $ModuleFields);
	}
}
