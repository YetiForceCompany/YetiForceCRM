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
	 * @return bool|int|array
	 */
	public function findAndBind()
	{
		$mailId = $this->mail->getMailCrmId();
		$recordId = 0;
		if ($mailId) {
			$returnIds = [];
			$query = (new \App\Db\Query())->select(['crmid'])->from('vtiger_ossmailview_relation')->where(['ossmailviewid' => $mailId]);
			$dataReader = $query->createCommand()->query();
			while ($crmId = $dataReader->readColumn(0)) {
				if (\App\Record::getType($crmId) === $this->moduleName) {
					$returnIds[] = $crmId;
				}
			}
			$dataReader->close();
			if (!empty($returnIds)) {
				$recordId = $returnIds;
			} else {
				$this->prefix = \App\Fields\Email::findRecordNumber($this->mail->get('subject'), $this->moduleName);
				if ($this->prefix) {
					$recordId = $this->add();
				} elseif (\App\Config::module('OSSMailScanner', 'SEARCH_PREFIX_IN_BODY') && $this->prefix = \App\Fields\Email::findRecordNumber($this->mail->get('body'), $this->moduleName, true)) {
					if ($this->prefix) {
						$recordId = $this->addByBody();
					}
				}
			}
		}
		return $recordId;
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
			$status = (new OSSMailView_Relation_Model())->addRelation($this->mail->getMailCrmId(), $crmId, $this->mail->get('udate_formated'));
			if ($status) {
				$returnIds[] = $crmId;
			}
		}
		return $returnIds;
	}

	/**
	 * Add by body.
	 *
	 * @return array
	 */
	protected function addByBody()
	{
		$returnIds = [];
		$crmId = false;
		$prefixes = implode(',', $this->prefix);
		if (\App\Cache::has('getRecordByPrefix', $prefixes)) {
			$crmId = \App\Cache::get('getRecordByPrefix', $prefixes);
		} else {
			$crmId = $this->getNewestRecord();
			if ($crmId) {
				\App\Cache::save('getRecordByPrefix', $prefixes, $crmId, \App\Cache::LONG);
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

	/**
	 * Get newest record id of given record prefixes.
	 *
	 * @return bool|int
	 */
	public function getNewestRecord()
	{
		$queryGenerator = new App\QueryGenerator($this->moduleName);
		$statusFieldName = \App\RecordStatus::getFieldName($this->moduleName);
		$queryGenerator->addCondition($statusFieldName,
		\App\RecordStatus::getStates($this->moduleName, \App\RecordStatus::RECORD_STATE_OPEN), 'e', false);
		$queryGenerator->addNativeCondition([$this->tableName . '.' . $this->tableColumn => $this->prefix]);
		$queryGenerator->setOrder('modifiedtime', 'DESC');
		return $queryGenerator->createQuery()->createCommand()->queryScalar() ?? false;
	}
}
