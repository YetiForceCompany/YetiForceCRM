<?php

/**
 * OSSMailView Relation mail
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_Relation_Model extends Vtiger_Relation_Model
{

	public function addRelation($sourceRecordId, $destinationRecordId)
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM vtiger_ossmailview_relation WHERE ossmailviewid = ? AND crmid = ?';
		$result = $db->pquery($query, [$sourceRecordId, $destinationRecordId]);
		if ($db->getRowCount($result) == 0) {
			$recordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, 'OSSMailView');
			$date = $recordModel->get('date');
			$db->insert('vtiger_ossmailview_relation', [
				'ossmailviewid' => $sourceRecordId,
				'crmid' => $destinationRecordId,
				'date' => $date
			]);
		}
	}
}
