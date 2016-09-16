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
		CRMEntity::trackLinkedInfo($crmid);
		$em = new VTEventsManager($db);
		$em->initTriggerCache();

		$destinationModuleName = \includes\Record::getType($crmid);
		$destinationModuleModel = Vtiger_Module_Model::getInstance($destinationModuleName);
		$data = [];
		$data['CRMEntity'] = $destinationModuleModel->focus;
		$data['entityData'] = VTEntityData::fromEntityId($db, $mailId);
		$data['sourceModule'] = $destinationModuleName;
		$data['sourceRecordId'] = $crmid;
		$data['destinationModule'] = 'OSSMailView';
		$data['destinationRecordId'] = $mailId;
		$em->triggerEvent('vtiger.entity.link.before', $data);
		$query = 'SELECT * FROM vtiger_ossmailview_relation WHERE ossmailviewid = ? && crmid = ?';
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

			if ($parentId = Users_Privileges_Model::getParentRecord($crmid)) {
				$query = 'SELECT * FROM vtiger_ossmailview_relation WHERE ossmailviewid = ? && crmid = ?';
				$result = $db->pquery($query, [$mailId, $parentId]);
				if ($db->getRowCount($result) == 0) {
					$db->insert('vtiger_ossmailview_relation', [
						'ossmailviewid' => $mailId,
						'crmid' => $parentId,
						'date' => $date
					]);
					if ($parentId = Users_Privileges_Model::getParentRecord($parentId)) {
						$query = 'SELECT * FROM vtiger_ossmailview_relation WHERE ossmailviewid = ? && crmid = ?';
						$result = $db->pquery($query, [$mailId, $parentId]);
						if ($db->getRowCount($result) == 0) {
							$db->insert('vtiger_ossmailview_relation', [
								'ossmailviewid' => $mailId,
								'crmid' => $parentId,
								'date' => $date
							]);
						}
					}
				}
			}
			$return = true;
		}
		$em->triggerEvent('vtiger.entity.link.after', $data);
		return $return;
	}
}
