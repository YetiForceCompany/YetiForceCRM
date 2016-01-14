<?php

/**
 * OSSMailView Relation mail
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_Relation_Model extends Vtiger_Relation_Model
{

	public function addRelation($mailId, $crmid, $date = false)
	{
		$return = false;
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM vtiger_ossmailview_relation WHERE ossmailviewid = ? AND crmid = ?';
		$result = $db->pquery($query, [$mailId, $crmid]);
		if ($db->getRowCount($result) == 0) {
			if (!$date) {
				$recordModel = Vtiger_Record_Model::getInstanceById($mailId, 'OSSMailView');
				$date = $recordModel->get('date');
			}
			$db->insert('vtiger_ossmailview_relation', [
				'ossmailviewid' => $mailId,
				'crmid' => $crmid,
				'date' => $date
			]);
			$return = true;
		}
		return $return;
	}
}
