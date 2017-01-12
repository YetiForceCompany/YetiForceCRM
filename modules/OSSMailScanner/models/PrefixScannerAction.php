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
			$type = \App\Record::getType($crmid);
			if ($type == $this->moduleName) {
				$returnIds[] = $crmid;
			}
		}
		if (!empty($returnIds)) {
			return $returnIds;
		}
		$this->prefix = \App\Fields\Email::findRecordNumber($this->mail->get('subject'), $this->moduleName);
		if (!$this->prefix) {
			return false;
		}

		return $this->add();
	}

	protected function add()
	{
		$returnIds = [];
		$crmId = false;
		if (\App\Cache::has('getRecordByPrefix', $this->prefix)) {
			$crmId = \App\Cache::get('getRecordByPrefix', $this->prefix);
		} else {
			$moduleObject = CRMEntity::getInstance($this->moduleName);
			$tableIndex = $moduleObject->tab_name_index[$this->tableName];
			$crmId = (new \App\Db\Query())
				->select([$tableIndex])
				->from($this->tableName)
				->innerJoin('vtiger_crmentity', "$this->tableName.$tableIndex = vtiger_crmentity.crmid")
				->where(['vtiger_crmentity.deleted' => 0, $this->tableName . '.' . $this->tableColumn => $this->prefix])
				->scalar();
			if ($crmId) {
				\App\Cache::save('getRecordByPrefix', $this->prefix, $crmId, \App\Cache::LONG);
			}
		}
		if ($crmId) {
			$status = (new OSSMailView_Relation_Model())->addRelation($this->mail->getMailCrmId(), $crmId, $this->mail->get('udate_formated'));
			if ($status) {
				$returnIds[] = $crmId;
			}
		}
		return $returnIds;
	}
}
