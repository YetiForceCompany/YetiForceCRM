<?php

namespace App\Fields;

/**
 * Record number class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class RecordNumber extends \App\Base
{
	/**
	 * Instance cache by module id.
	 *
	 * @var \App\Fields\RecordNumber[]
	 */
	private static array $instanceCache = [];
	/**
	 * Sequence number field cache by module id.
	 *
	 * @var array
	 */
	private static array $sequenceNumberFieldCache = [];

	/**
	 * Related value.
	 *
	 * @var string
	 */
	private $relatedValue;

	/**
	 * Function to get instance.
	 *
	 * @param int|string $tabId
	 *
	 * @return \App\Fields\RecordNumber
	 */
	public static function getInstance($tabId): self
	{
		if (!is_numeric($tabId)) {
			$tabId = \App\Module::getModuleId($tabId);
		}
		if (isset(static::$instanceCache[$tabId])) {
			return static::$instanceCache[$tabId];
		}
		$instance = new static();
		$row = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => $tabId])->one() ?: [];
		$row['tabid'] = $tabId;
		$instance->setData($row);

		return static::$instanceCache[$tabId] = $instance;
	}

	/**
	 * Get data for update.
	 *
	 * @return $this
	 */
	public function getNumDataForUpdate()
	{
		$sql = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => $this->get('tabid')])->createCommand()->getRawSql() . ' FOR UPDATE ';
		$row = \App\Db::getInstance()->createCommand($sql)->queryOne();
		$row['tabid'] = $this->get('tabid');

		return $this->setData(array_merge($this->getData(), $row));
	}

	/**
	 * Sets model of record.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return $this
	 */
	public function setRecord(\Vtiger_Record_Model $recordModel): self
	{
		$this->set('recordModel', $recordModel);
		return $this;
	}

	/**
	 * Returns model of record.
	 *
	 * @return \Vtiger_Record_Model|null
	 */
	public function getRecord(): ?\Vtiger_Record_Model
	{
		return $this->get('recordModel');
	}

	/**
	 * Function to get the next nuber of record.
	 *
	 * @return string
	 */
	public function getIncrementNumber(): string
	{
		$value = $this->getRelatedValue();
		$this->getNumDataForUpdate();
		$actualSequence = static::getSequenceNumber($this->get('reset_sequence'));
		if ($this->get('reset_sequence') && $this->get('cur_sequence') !== $actualSequence) {
			$currentSequenceNumber = 1;
			$this->updateModuleVariablesSequences($currentSequenceNumber);
		} else {
			$currentSequenceNumber = $this->get('cur_id');
			if ($value) {
				$sql = (new \App\Db\Query())->select(['cur_id'])->from('u_#__modentity_sequences')->where(['tabid' => $this->get('tabid'), 'value' => $value])->createCommand()->getRawSql() . ' FOR UPDATE ';
				$currentSequenceNumber = \App\Db::getInstance()->createCommand($sql)->queryScalar() ?: 1;
			}
		}

		$reqNo = $currentSequenceNumber + 1;
		$this->setNumberSequence($reqNo, $actualSequence);
		$fullPrefix = $this->parseNumber($currentSequenceNumber);

		return \App\Purifier::decodeHtml($fullPrefix);
	}

	/**
	 * Gets related value.
	 *
	 * @param bool $reload
	 *
	 * @return string
	 */
	public function getRelatedValue(bool $reload = false): string
	{
		if (!isset($this->relatedValue) || $reload) {
			$value = [];
			preg_match_all('/{{picklist:([a-zA-Z0-9_]+)}}|\$\((\w+) : ([,"\+\#\%\.\=\-\[\]\&\w\s\|\)\(\:]+)\)\$/u', $this->get('prefix') . $this->get('postfix'), $matches);
			if ($this->getRecord() && !empty($matches[0])) {
				foreach ($matches[0] as $key => $element) {
					if (0 === strpos($element, '{{picklist:')) {
						$value[] = $this->getPicklistValue($matches[1][$key]);
					} else {
						$value[] = \App\TextParser::getInstanceByModel($this->getRecord())->setGlobalPermissions(false)->setContent($element)->parse()->getContent();
					}
				}
			}
			$this->relatedValue = implode('|', $value);
		}
		return $this->relatedValue;
	}

	/**
	 * Parse number based on postfix and prefix.
	 *
	 * @param int $seq
	 *
	 * @return string
	 */
	public function parseNumber(int $seq): string
	{
		$string = str_replace(['{{YYYY}}', '{{YY}}', '{{MM}}', '{{M}}', '{{DD}}', '{{D}}'], [static::date('Y'), static::date('y'), static::date('m'), static::date('n'), static::date('d'), static::date('j')], $this->get('prefix') . str_pad((string) $seq, $this->get('leading_zeros'), '0', STR_PAD_LEFT) . $this->get('postfix'));
		if ($this->getRecord()) {
			$string = \App\TextParser::getInstanceByModel($this->getRecord())->setGlobalPermissions(false)->setContent($string)->parse()->getContent();
		}
		return preg_replace_callback('/{{picklist:([a-z0-9_]+)}}/i', fn ($matches) => $this->getRecord() ? $this->getPicklistValue($matches[1]) : $matches[1], $string);
	}

	/**
	 * Sets number of sequence.
	 *
	 * @param int    $reqNo
	 * @param string $actualSequence
	 *
	 * @throws \yii\db\Exception
	 */
	public function setNumberSequence(int $reqNo, string $actualSequence)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$data = ['cur_sequence' => $actualSequence];
		$this->set('cur_sequence', $actualSequence);
		if ($value = $this->getRelatedValue()) {
			$dbCommand->upsert('u_#__modentity_sequences', ['tabid' => $this->get('tabid'),	'value' => $value,	'cur_id' => $reqNo], ['cur_id' => $reqNo])->execute();
		} else {
			$data['cur_id'] = $reqNo;
			$this->set('cur_id', $reqNo);
		}
		$dbCommand->update('vtiger_modentity_num', $data, ['tabid' => $this->get('tabid')])->execute();
	}

	/**
	 * Update module variables sequences.
	 *
	 * @param int   $currentId
	 * @param array $conditions
	 *
	 * @return void
	 */
	public function updateModuleVariablesSequences($currentId, $conditions = []): void
	{
		$conditions['tabid'] = $this->get('tabid');
		\App\Db::getInstance()->createCommand()->update('u_#__modentity_sequences', ['cur_id' => $currentId], $conditions)->execute();
	}

	/**
	 * Function to check if record need a new number of sequence.
	 *
	 * @return bool
	 */
	public function isNewSequence(): bool
	{
		return $this->getRecord()->isNew()
			|| ($this->getRelatedValue() && $this->getRelatedValue() !== (clone $this)
				->setRecord((clone $this->getRecord())->getInstanceByEntity($this->getRecord()->getEntity(), $this->getRecord()->getId()))->getRelatedValue(true));
	}

	/**
	 * Updates missing numbers of records.
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return array
	 */
	public function updateRecords()
	{
		if ($this->isEmpty('id')) {
			return [];
		}
		$moduleModel = \Vtiger_Module_Model::getInstance($this->get('tabid'));
		$fieldModel = current($moduleModel->getFieldsByUiType(4));
		$returninfo = [];
		if ($fieldModel) {
			$fieldTable = $fieldModel->getTableName();
			$fieldColumn = $fieldModel->getColumnName();
			if ($fieldTable === $moduleModel->getEntityInstance()->table_name) {
				$picklistName = $this->getPicklistName();
				$queryGenerator = new \App\QueryGenerator(\App\Module::getModuleName($this->get('tabid')));
				$queryGenerator->setFields([$picklistName, $fieldModel->getName(), 'id']);
				if (\App\TextParser::isVaribleToParse($this->get('prefix') . $this->get('postfix'))) {
					$queryGenerator->setFields(array_keys($queryGenerator->getModuleFields()))->setField('id');
				}
				$queryGenerator->permissions = false;
				$queryGenerator->addNativeCondition(['or', [$fieldColumn => ''], [$fieldColumn => null]]);
				$dataReader = $queryGenerator->createQuery()->createCommand()->query();
				$totalCount = $dataReader->count();
				if ($totalCount) {
					$returninfo['totalrecords'] = $totalCount;
					$returninfo['updatedrecords'] = 0;
					$sequenceNumber = $this->get('cur_id');
					$oldNumber = $sequenceNumber;
					$oldSequences = $sequences = (new \App\Db\Query())->select(['value', 'cur_id'])->from('u_#__modentity_sequences')->where(['tabid' => $this->get('tabid')])->createCommand()->queryAllByGroup();
					$dbCommand = \App\Db::getInstance()->createCommand();
					while ($recordInfo = $dataReader->read()) {
						$this->setRecord($moduleModel->getRecordFromArray($recordInfo));
						$seq = 0;
						$value = $this->getRelatedValue(true);
						if ($value && isset($sequences[$value])) {
							$seq = $sequences[$value]++;
						} elseif ($value) {
							$sequences[$value] = 1;
							$seq = $sequences[$value]++;
						} else {
							$seq = $sequenceNumber++;
						}
						$dbCommand->update($fieldTable, [$fieldColumn => \App\Purifier::decodeHtml($this->parseNumber($seq))], [$moduleModel->getEntityInstance()->table_index => $recordInfo['id']])
							->execute();
						++$returninfo['updatedrecords'];
					}
					$dataReader->close();
					if ($oldNumber != $sequenceNumber) {
						$dbCommand->update('vtiger_modentity_num', ['cur_id' => $sequenceNumber], ['tabid' => $this->get('tabid')])->execute();
					}
					foreach (array_diff($sequences, $oldSequences) as $prefix => $num) {
						$dbCommand->update('u_#__modentity_sequences', ['cur_id' => $num], ['value' => $prefix, 'tabid' => $this->get('tabid')])
							->execute();
					}
				}
			} else {
				\App\Log::error('Updating Missing Sequence Number FAILED! REASON: Field table and module table mismatching.');
			}
		}
		return $returninfo;
	}

	/**
	 * Date function that can be overrided in tests.
	 *
	 * @param string   $format
	 * @param int|null $time
	 *
	 * @return false|string
	 */
	public static function date($format, $time = null)
	{
		if (null === $time) {
			$time = time();
		}
		return date($format, $time);
	}

	/**
	 * Get sequence number that should be saved.
	 *
	 * @param string $resetSequence one character
	 *
	 * @return string
	 */
	public static function getSequenceNumber($resetSequence): string
	{
		switch ($resetSequence) {
			case 'Y':
				$currentSeqId = static::date('Y');
				break;
			case 'M':
				$currentSeqId = static::date('Ym'); // with year because 2016-10 (10) === 2017-10 (10) and number will be incremented but should be set to 1 (new year)
				break;
			case 'D':
				$currentSeqId = static::date('Ymd'); // same as above because 2016-10-03 (03) === 2016-11-03 (03)
				break;
			default:
				$currentSeqId = '';
		}

		return $currentSeqId;
	}

	/**
	 * Saves configuration.
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function save()
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		if ($this->isEmpty('id')) {
			return $dbCommand->insert('vtiger_modentity_num', [
				'tabid' => $this->get('tabid'),
				'prefix' => $this->get('prefix'),
				'leading_zeros' => $this->get('leading_zeros') ?: 0,
				'postfix' => $this->get('postfix') ?: '',
				'start_id' => $this->get('cur_id'),
				'cur_id' => $this->get('cur_id'),
				'reset_sequence' => $this->get('reset_sequence'),
				'cur_sequence' => $this->get('cur_sequence'),
			])->execute();
		}
		return $dbCommand->update(
			'vtiger_modentity_num',
			[
				'cur_id' => $this->get('cur_id'),
				'prefix' => $this->get('prefix'),
				'leading_zeros' => $this->get('leading_zeros'),
				'postfix' => $this->get('postfix'),
				'reset_sequence' => $this->get('reset_sequence'),
				'cur_sequence' => $this->get('cur_sequence'), ],
			['tabid' => $this->get('tabid')]
		)
			->execute();
	}

	/**
	 * Get sequence number field name.
	 *
	 * @param int $tabId
	 *
	 * @return string|bool
	 */
	public static function getSequenceNumberFieldName(int $tabId)
	{
		return self::getSequenceNumberField($tabId)['fieldname'] ?? '';
	}

	/**
	 * Get sequence number field.
	 *
	 * @param int $tabId
	 *
	 * @return string[]|bool
	 */
	public static function getSequenceNumberField(int $tabId)
	{
		if (isset(self::$sequenceNumberFieldCache[$tabId])) {
			return self::$sequenceNumberFieldCache[$tabId];
		}
		return self::$sequenceNumberFieldCache[$tabId] = (new \App\Db\Query())->select(['fieldname', 'columnname', 'tablename'])->from('vtiger_field')
			->where(['tabid' => $tabId, 'uitype' => 4, 'presence' => [0, 2]])->one();
	}

	/**
	 * Clean sequence number field cache.
	 *
	 * @param int|null $tabId
	 *
	 * @return void
	 */
	public static function cleanSequenceNumberFieldCache(?int $tabId)
	{
		if ($tabId) {
			unset(self::$sequenceNumberFieldCache[$tabId]);
		}
		self::$sequenceNumberFieldCache = null;
	}

	/**
	 * Returns name of picklist. Postfix or prefix can contains name of picklist.
	 *
	 * @return string
	 */
	private function getPicklistName(): string
	{
		preg_match('/{{picklist:([a-z0-9_]+)}}/i', $this->get('prefix') . $this->get('postfix'), $matches);
		return $matches[1] ?? '';
	}

	/**
	 * Returns prefix of picklist.
	 *
	 * @param string      $picklistName
	 * @param string|null $recordValue
	 *
	 * @return string
	 */
	private function getPicklistValue(string $picklistName, ?string $recordValue = null): string
	{
		$values = Picklist::getValues($picklistName);
		if (null === $recordValue) {
			$recordValue = $this->getRecord()->get($picklistName);
		}
		foreach ($values as $value) {
			if ($recordValue === $value[$picklistName]) {
				return $value['prefix'] ?? '';
			}
		}
		return '';
	}
}
