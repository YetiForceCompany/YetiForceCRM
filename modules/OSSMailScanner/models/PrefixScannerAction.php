<?php
/**
 * Base for action creating relations on the basis of prefix.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Base for action creating relations on the basis of prefix.
 */
abstract class OSSMailScanner_PrefixScannerAction_Model
{
	public $prefix;
	public $moduleName;
	public $mail;
	public $tableName;
	public $tableColumn;

	/**
	 * Process.
	 *
	 * @param OSSMail_Mail_Model $mail
	 */
	abstract public function process(OSSMail_Mail_Model $mail);

	/**
	 * Find and bind.
	 *
	 * @return bool|int
	 */
	public function findAndBind()
	{
		if (!($mailId = $this->mail->getMailCrmId())) {
			return 0;
		}
		$returnIds = (new \App\Db\Query())->select(['vtiger_ossmailview_relation.crmid'])
			->from('vtiger_ossmailview_relation')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_ossmailview_relation.crmid')
			->where(['ossmailviewid' => $mailId, 'setype' => $this->moduleName])->column();
		if (!empty($returnIds)) {
			return $returnIds;
		}
		$returnIds = false;
		$this->prefix = \App\Fields\Email::findRecordNumber($this->mail->get('subject'), $this->moduleName);
		if ($this->prefix) {
			$recordId = $this->add();
		} elseif (\App\Config::module('OSSMailScanner', 'SEARCH_PREFIX_IN_BODY') && $this->prefix = \App\Fields\Email::findRecordNumber($this->mail->get('body'), $this->moduleName, true)) {
			$recordId = $this->addByBody();
		}

		$this->prefix = \App\Mail\RecordFinder::getRecordNumberFromString($this->mail->get('subject'), $this->moduleName);
		if ($this->prefix) {
			$returnIds = $this->add();
		} elseif (\App\Config::module('OSSMailScanner', 'SEARCH_PREFIX_IN_BODY') && ($this->prefix = \App\Mail\RecordFinder::getRecordNumberFromString($this->mail->get('body'), $this->moduleName, true))) {
			$recordId = $this->addByBody();
		}
		return $this->add();
	}

	/**
	 * Add.
	 *
	 * @return array
	 */
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
			$status = (new OSSMailView_Relation_Model())->addRelation($this->mail->getMailCrmId(), $crmId, $this->mail->get('date'));
			if ($status) {
				$returnIds[] = $crmId;
			}
		}
		return $returnIds;
	}
}
