<?php

/**
 * Base for action creating relations on the basis of prefix
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_PrefixScannerAction_Model
{

	public function process($mail, $moduleName, $tableName, $tableColumn)
	{
		$db = PearDatabase::getInstance();
		$mailId = $mail->getMailCrmId();
		if (!$mailId) {
			return 0;
		}
		$returnIds = [];
		$result = $db->pquery('SELECT crmid FROM vtiger_ossmailview_relation WHERE ossmailviewid = ?;', [$mailId]);
		while ($crmid = $db->getSingleValue($result)) {
			$type = \includes\Record::getType($crmid);
			if ($type == $moduleName) {
				$returnIds[] = $crmid;
			}
		}
		if (!empty($returnIds)) {
			return $returnIds;
		}

		$prefix = \includes\fields\Email::findRecordNumber($mail->get('subject'), $moduleName);
		if (!$prefix) {
			return false;
		}
		$cache = Vtiger_Cache::get('MSFindPrevix', $prefix);
		if ($cache !== false) {
			$status = OSSMailView_Relation_Model::addRelation($mailId, $cache, $mail->get('udate_formated'));
			if ($status) {
				$returnIds[] = $cache;
			}
		} else {
			$moduleObject = CRMEntity::getInstance($moduleName);
			$tableIndex = $moduleObject->tab_name_index[$tableName];

			$query = sprintf('SELECT %s FROM %s INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = %s.%s WHERE vtiger_crmentity.deleted = 0  && %s = ? ', $tableIndex, $tableName, $tableName, $tableIndex, $tableName . '.' . $tableColumn);
			$result = $db->pquery($query, [$prefix]);
			if ($db->getRowCount($result)) {
				$crmid = $db->getSingleValue($result);

				$status = OSSMailView_Relation_Model::addRelation($mailId, $crmid, $mail->get('udate_formated'));
				if ($status) {
					$returnIds[] = $crmid;
				}
				Vtiger_Cache::set('MSFindPrevix', $prefix, $crmid);
			}
		}
		return $returnIds;
	}
}
