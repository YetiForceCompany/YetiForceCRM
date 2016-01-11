<?php

/**
 * Base for action creating relations on the basis of prefix
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_PrefixScannerAction_Model extends OSSMailScanner_BaseScannerAction_Model
{

	public function process($mail, $moduleName, $tableName, $tableColumn)
	{
		$db = PearDatabase::getInstance();
		$mailId = $mail->getMailCrmId();
		if (!$mailId) {
			return 0;
		}

		$relationExist = false;
		$relationExistResult = $db->pquery('SELECT crmid FROM vtiger_ossmailview_relation WHERE ossmailviewid = ?;', [$mailId]);
		while ($crmid = $db->getSingleValue($result)) {
			$type = Vtiger_Functions::getCRMRecordType($crmid);
			if ($type == $moduleName) {
				$relationExist = true;
			}
		}
		if ($relationExist) {
			return false;
		}

		$prefix = $this->findEmailNumPrefix($moduleName, $mail->get('subject'));
		if (!$prefix) {
			return false;
		}

		require_once("modules/$moduleName/$moduleName.php");
		$moduleObject = new $moduleName();
		$tableIndex = $moduleObject->tab_name_index[$tableName];

		$returnIds = [];
		$result = $db->pquery('SELECT ' . $tableIndex . ' FROM ' . $tableName . ' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = ' . $tableName . '.' . $tableIndex . ' WHERE vtiger_crmentity.deleted = 0  AND ' . $tableColumn . ' = ? ', [$prefix]);

		if ($db->getRowCount($result) > 0) {
			$crmid = $db->getSingleValue($result);

			$resultRelation = $db->pquery('SELECT crmid FROM vtiger_ossmailview_relation WHERE ossmailviewid=? AND crmid=?', [$mailId, $crmid]);
			if ($db->getRowCount($resultRelation) == 0) {
				$db->insert('vtiger_ossmailview_relation', [
					'ossmailviewid' => $mailId,
					'crmid' => $crmid,
					'date' => $mail->get('udate_formated')
				]);
				$returnIds[] = $crmid;
			}
		}

		return $returnIds;
	}
}
