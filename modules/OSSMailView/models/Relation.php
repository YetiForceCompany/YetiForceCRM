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
		$destinationModuleName = \App\Record::getType($crmid);
		$data = [
			'CRMEntity' => CRMEntity::getInstance($destinationModuleName),
			'sourceModule' => $destinationModuleName,
			'sourceRecordId' => $crmid,
			'destinationModule' => 'OSSMailView',
			'destinationRecordId' => $mailId
		];
		$eventHandler = new App\EventHandler();
		$eventHandler->setModuleName($destinationModuleName);
		$eventHandler->setParams($data);
		$eventHandler->trigger('EntityBeforeLink');

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
		$eventHandler->trigger('EntityAfterLink');
		return $return;
	}

	public function getAttachments()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['LEFT JOIN', 'vtiger_seattachmentsrel', 'vtiger_seattachmentsrel.crmid = vtiger_notes.notesid']);
		$queryGenerator->addJoin(['LEFT JOIN', 'vtiger_attachments', 'vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid']);
		$queryGenerator->addJoin(['LEFT JOIN', 'vtiger_ossmailview_files', 'vtiger_ossmailview_files.documentsid = vtiger_notes.notesid']);
		$queryGenerator->addNativeCondition(['vtiger_ossmailview_files.ossmailviewid' => $this->get('parentRecord')->getId()]);
	}
}
