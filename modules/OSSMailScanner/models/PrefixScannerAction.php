<?php

/**
 * Base for action creating relations on the basis of prefix
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
abstract class OSSMailScanner_PrefixScannerAction_Model
{

	public $prefix, $moduleName, $mail, $tableName, $tableColumn;

	public abstract function process(OSSMail_Mail_Model $mail);

	public function findAndBind()
	{
		$db = PearDatabase::getInstance();
		$mailId = $this->mail->getMailCrmId();
		if (!$mailId) {
			return 0;
		}
		$returnIds = [];
		$result = $db->pquery('SELECT crmid FROM vtiger_ossmailview_relation WHERE ossmailviewid = ?;', [$mailId]);
		while ($crmid = $db->getSingleValue($result)) {
			$type = \includes\Record::getType($crmid);
			if ($type == $this->moduleName) {
				$returnIds[] = $crmid;
			}
		}
		if (!empty($returnIds)) {
			return $returnIds;
		}
		$this->prefix = \includes\fields\Email::findRecordNumber($this->mail->get('subject'), $this->moduleName);
		if (!$this->prefix) {
			return false;
		}

		return $this->add();
	}

	protected function add()
	{
		$returnIds = [];
		$cache = Vtiger_Cache::get('MSFindPrevix', $this->prefix);
		if ($cache !== false) {
			$status = OSSMailView_Relation_Model::addRelation($this->mail->getMailCrmId(), $cache, $this->mail->get('udate_formated'));
			if ($status) {
				$returnIds[] = $cache;
			}
		} else {
			$moduleObject = CRMEntity::getInstance($this->moduleName);
			$tableIndex = $moduleObject->tab_name_index[$this->tableName];
			$db = PearDatabase::getInstance();
			$query = sprintf('SELECT %s FROM %s INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = %s.%s WHERE vtiger_crmentity.deleted = 0  && %s = ? ', $tableIndex, $this->tableName, $this->tableName, $tableIndex, $this->tableName . '.' . $this->tableColumn);
			$result = $db->pquery($query, [$this->prefix]);
			if ($db->getRowCount($result)) {
				$crmid = $db->getSingleValue($result);
				$status = OSSMailView_Relation_Model::addRelation($this->mail->getMailCrmId(), $crmid, $this->mail->get('udate_formated'));
				if ($status) {
					$returnIds[] = $crmid;
				}
				Vtiger_Cache::set('MSFindPrevix', $this->prefix, $crmid);
			}
		}
		return $returnIds;
	}
}
