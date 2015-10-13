<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

function bind_prefix($user_id, $mail_detail, $folder, $moduleName, $table_name, $table_col, $ossmailviewTab)
{
	$adb = PearDatabase::getInstance();
	if ($mail_detail['ossmailviewid'] == '') {
		$result_ossmailview = $adb->pquery("SELECT ossmailviewid FROM vtiger_ossmailview where uid = ? AND rc_user = ? ", [$mail_detail['message_id'], $user_id]);
		if ($adb->num_rows($result_ossmailview) == 0) {
			return FALSE;
		}
		$mailViewId = $adb->query_result($result_ossmailview, 0, 'ossmailviewid');
	} else {
		$mailViewId = $mail_detail['ossmailviewid'];
	}
	$relationExist = false;
	$relationExistResult = $adb->pquery("SELECT crmid FROM vtiger_ossmailview_relation WHERE ossmailviewid = ?;", [$mailViewId]);
	for ($i = 0; $i < $adb->num_rows($relationExistResult); $i++) {
		$crmid = $adb->query_result_raw($relationExistResult, $i, 'crmid');
		$type = Vtiger_Functions::getCRMRecordType($crmid);
		if ($type == $moduleName) {
			$relationExist = TRUE;
		}
	}
	if ($relationExist) {
		return false;
	}

	$emailNumPrefix = OSSMailScanner_Record_Model::findEmailNumPrefix($moduleName, $mail_detail['subject']);
	if (!$emailNumPrefix) {
		return false;
	}

	require_once("modules/$moduleName/$moduleName.php");
	$moduleObject = new $moduleName();
	$tableIndex = $moduleObject->table_index;

	$return_id = [];
	$result = $adb->pquery("SELECT $tableIndex FROM " . $table_name . " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = " . $table_name . "." . $tableIndex . " WHERE vtiger_crmentity.deleted = 0  AND " . $table_col . " = ? ", [$emailNumPrefix]);

	if ($adb->num_rows($result) > 0) {
		$crmid = $adb->getSingleValue($result);

		$resultRelation = $adb->pquery('SELECT * FROM vtiger_ossmailview_relation WHERE ossmailviewid=? AND crmid=?', [$mailViewId, $crmid]);
		if ($resultRelation->rowCount() == 0) {
			$adb->pquery('INSERT INTO vtiger_ossmailview_relation SET ossmailviewid=?, crmid=?, date=?', [$mailViewId, $crmid, $mail_detail['udate_formated']]);
			$return_id[] = $crmid;
		}
	}
	return $return_id;
}
