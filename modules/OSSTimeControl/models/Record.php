<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

Class OSSTimeControl_Record_Model extends Vtiger_Record_Model
{

	const recalculateStatus = 'Accepted';
	public static $referenceFieldsToTime = ['link', 'process', 'subprocess'];

	public static function recalculateTimeControl($id, $name)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT SUM(sum_time) as sum FROM vtiger_osstimecontrol INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_osstimecontrol.osstimecontrolid WHERE vtiger_crmentity.deleted = ? AND osstimecontrol_status = ? AND `$name` = ?;", [0, self::recalculateStatus, $id]);
		$sumTime = number_format($db->getSingleValue($result), 2);
		$metaData = Vtiger_Functions::getCRMRecordMetadata($id);
		$focus = CRMEntity::getInstance($metaData['setype']);
		$table = $focus->table_name;
		$result = $db->pquery("SHOW COLUMNS FROM `$table` LIKE 'sum_time';");
		if ($result->rowCount()) {
			$db->update($table, ['sum_time' => $sumTime], '`' . $focus->table_index . '` = ?', [$id]);
		}
	}

	public function getProjectRelatedIDS($ProjectID)
	{
		if (!self::checkID($ProjectID)) {
			return false;
		}
		$db = PearDatabase::getInstance();
		//////// sum_time
		$projectIDS = array();
		$sum_time_result = $db->pquery("SELECT osstimecontrolid FROM vtiger_osstimecontrol WHERE deleted = ? AND osstimecontrol_status = ? AND projectid = ? AND projecttaskid = ? AND ticketid = ?;", array(0, self::recalculateStatus, $ProjectID, 0, 0), true);
		for ($i = 0; $i < $db->num_rows($sum_time_result); $i++) {
			$projectIDS[] = $db->query_result($sum_time_result, $i, 'osstimecontrolid');
		}
		//////// sum_time_h
		$ticketsIDS = array();
		$sql_sum_time_h = 'SELECT osstimecontrolid 
						FROM vtiger_osstimecontrol 
						INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.ticketid = vtiger_osstimecontrol.ticketid
						WHERE vtiger_osstimecontrol.deleted = ? 
						AND vtiger_osstimecontrol.ticketid <> ? 
						AND osstimecontrol_status = ?
						AND vtiger_troubletickets.projectid = ?;';
		$sum_time_h_result = $db->pquery($sql_sum_time_h, array(0, 0, self::recalculateStatus, $ProjectID), true);
		for ($i = 0; $i < $db->num_rows($sum_time_h_result); $i++) {
			$ticketsIDS[] = $db->query_result($sum_time_h_result, $i, 'osstimecontrolid');
		}
		//////// sum_time_pt
		$taskIDS = array();
		$sql_sum_time_pt = 'SELECT osstimecontrolid 
						FROM vtiger_osstimecontrol 
						INNER JOIN vtiger_projecttask ON vtiger_projecttask.projecttaskid = vtiger_osstimecontrol.projecttaskid
						WHERE vtiger_osstimecontrol.deleted = ? 
						AND vtiger_osstimecontrol.projecttaskid <> ? 
						AND vtiger_osstimecontrol.ticketid = ? 
						AND vtiger_osstimecontrol.projectid = ?
						AND vtiger_osstimecontrol.osstimecontrol_status = ?
						AND vtiger_projecttask.projectid = ?;';
		$sum_time_pt_result = $db->pquery($sql_sum_time_pt, array(0, 0, 0, 0, self::recalculateStatus, $ProjectID), true);
		for ($i = 0; $i < $db->num_rows($sum_time_pt_result); $i++) {
			$taskIDS[] = $db->query_result($sum_time_pt_result, $i, 'osstimecontrolid');
		}
		return array($taskIDS, $ticketsIDS, $projectIDS);
	}

	public static function checkID($ID)
	{
		if ($ID == 0 || $ID == '') {
			return false;
		}
		return true;
	}

	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();
		$date = new DateTime();
		$currDate = DateTimeField::convertToUserFormat($date->format('Y-m-d'));

		$time = $date->format('H:i');

		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true&date_start='
			. $currDate . '&due_date=' . $currDate . '&time_start=' . $time . '&time_end=' . $time;
	}
}
