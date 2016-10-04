<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

Class OSSTimeControl_Record_Model extends Vtiger_Record_Model
{

	const recalculateStatus = 'Accepted';

	public static $referenceFieldsToTime = ['link', 'process', 'subprocess'];

	public static function recalculateTimeControl($id, $name)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT SUM(sum_time) as sum FROM vtiger_osstimecontrol INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_osstimecontrol.osstimecontrolid WHERE vtiger_crmentity.deleted = ? && osstimecontrol_status = ? && `$name` = ?;", [0, self::recalculateStatus, $id]);
		$sumTime = number_format($db->getSingleValue($result), 2);
		$metaData = vtlib\Functions::getCRMRecordMetadata($id);
		$focus = CRMEntity::getInstance($metaData['setype']);
		$table = $focus->table_name;
		$result = $db->pquery("SHOW COLUMNS FROM `$table` LIKE 'sum_time';");
		if ($result->rowCount()) {
			$db->update($table, ['sum_time' => $sumTime], '`' . $focus->table_index . '` = ?', [$id]);
		}
	}

	public static function setSumTime($data)
	{
		$db = PearDatabase::getInstance();
		$start = strtotime(DateTimeField::convertToDBFormat($data->get('date_start')) . ' ' . $data->get('time_start'));
		$end = strtotime(DateTimeField::convertToDBFormat($data->get('due_date')) . ' ' . $data->get('time_end'));
		$time = round(abs($end - $start) / 3600, 2);
		$db->update('vtiger_osstimecontrol', ['sum_time' => $time], '`osstimecontrolid` = ?', [$data->getId()]);
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
