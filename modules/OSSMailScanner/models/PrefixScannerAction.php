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
			$type = vtlib\Functions::getCRMRecordType($crmid);
			if ($type == $moduleName) {
				$returnIds[] = $crmid;
			}
		}
		if (count($returnIds) > 0) {
			return $returnIds;
		}

		$prefix = includes\fields\Email::findCrmidByPrefix($mail->get('subject'), $moduleName);
		if (!$prefix) {
			return false;
		}

		$name = 'MSFindPrevix';
		$cache = Vtiger_Cache::get($name, $prefix);
		if ($cache !== false) {
			$status = OSSMailView_Relation_Model::addRelation($mailId, $cache, $mail->get('udate_formated'));
			if ($status) {
				$returnIds[] = $cache;
			}
			return $returnIds;
		} else {
			require_once("modules/$moduleName/$moduleName.php");
			$moduleObject = new $moduleName();
			$tableIndex = $moduleObject->tab_name_index[$tableName];

			$query = sprintf('SELECT %s FROM %s INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = %s.%s WHERE vtiger_crmentity.deleted = 0  AND %s = ? ', $tableIndex, $tableName, $tableName, $tableIndex, $tableColumn);
			$result = $db->pquery($query, [$prefix]);

			if ($db->getRowCount($result) > 0) {
				$crmid = $db->getSingleValue($result);

				$status = OSSMailView_Relation_Model::addRelation($mailId, $crmid, $mail->get('udate_formated'));
				if ($status) {
					$returnIds[] = $crmid;
				}
			}
			return $returnIds;
		}
	}
}
