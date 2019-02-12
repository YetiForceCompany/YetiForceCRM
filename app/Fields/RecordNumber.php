<?php

namespace App\Fields;

/**
 * Record number class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class RecordNumber extends \App\Base
{
	/**
	 * Function to get instance.
	 *
	 * @param string|int $tabId
	 *
	 * @return \App\Fields\RecordNumber
	 */
	public static function getInstance($tabId): self
	{
		$instance = new static();
		if (!\is_numeric($tabId)) {
			$tabId = \App\Module::getModuleId($tabId);
		}
		$row = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => $tabId])->one();
		$row['tabid'] = $tabId;
		$instance->setData($row);
		return $instance;
	}

	/**
	 * Sets model of record.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public function setRecord(\Vtiger_Record_Model $recordModel)
	{
		$this->set('recordModel', $recordModel);
	}

	/**
	 * Returns model of record.
	 *
	 * @return mixed
	 */
	public function getRecord()
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
		$actualSequence = static::getSequenceNumber($this->get('reset_sequence'));
		if ($this->get('reset_sequence') && $this->get('cur_sequence') !== $actualSequence) {
			$currentSequenceNumber = 1;
		} else {
			$currentSequenceNumber = $this->getCurrentSequenceNumber();
		}

		$fullPrefix = $this->parseNumber($currentSequenceNumber);
		$strip = strlen($currentSequenceNumber) - strlen($currentSequenceNumber + 1);
		if ($strip < 0) {
			$strip = 0;
		}
		$temp = str_repeat('0', $strip);
		$reqNo = $temp . ($currentSequenceNumber + 1);

		$this->setNumberSequence($reqNo, $actualSequence);
		return \App\Purifier::decodeHtml($fullPrefix);
	}

	/**
	 * Function to get current sequence number.
	 *
	 * @return int
	 */
	private function getCurrentSequenceNumber(): int
	{
		if (!($piclistName = $this->getPicklistName()) || !$this->getPicklistValue($piclistName)) {
			return $this->get('cur_id');
		}
		return (new \App\Db\Query())->select(['cur_id'])->from('u_#__modentity_sequences')->where(['value' => $this->getPicklistValue($piclistName)])->scalar() ?: 1;
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
	 * Parse nummber based on postfix and prefix.
	 *
	 * @param int $seq
	 *
	 * @return string
	 */
	public function parseNumber(int $seq): string
	{
		return preg_replace_callback('/{{picklist:([a-z0-9_]+)}}/i', function ($matches) {
			return $this->getRecord() ? $this->getPicklistValue($matches[1]) : $matches[1];
		}, str_replace(['{{YYYY}}', '{{YY}}', '{{MM}}', '{{M}}', '{{DD}}', '{{D}}'], [static::date('Y'), static::date('y'), static::date('m'), static::date('n'), static::date('d'), static::date('j')], $this->get('prefix') . str_pad((string) $seq, $this->get('leading_zeros'), '0', STR_PAD_LEFT) . $this->get('postfix')));
	}

	/**
	 * Sets number of sequence.
	 *
	 * @param int    $reqNo
	 * @param string $actualSequence
	 *
	 * @throws \yii\db\Exception
	 */
	private function setNumberSequence(int $reqNo, string $actualSequence)
	{
		$data = ['cur_sequence' => $actualSequence];
		$this->set('cur_sequence', $actualSequence);
		if (!($piclistName = $this->getPicklistName()) || !$this->getPicklistValue($piclistName)) {
			$data['cur_id'] = $reqNo;
			$this->set('cur_id', $reqNo);
		} else {
			if ((new \App\Db\Query())->from('u_#__modentity_sequences')->where(['value' => $this->getPicklistValue($piclistName), 'tabid' => $this->get('tabid')])->exists()) {
				\App\Db::getInstance()->createCommand()->update('u_#__modentity_sequences', ['cur_id' => $reqNo], ['value' => $this->getPicklistValue($piclistName), 'tabid' => $this->get('tabid')])->execute();
			} else {
				\App\Db::getInstance()->createCommand()->insert('u_#__modentity_sequences', ['cur_id' => $reqNo, 'tabid' => $this->get('tabid'), 'value' => $this->getPicklistValue($piclistName)])->execute();
			}
		}
		\App\Db::getInstance()->createCommand()->update('vtiger_modentity_num', $data, ['tabid' => $this->get('tabid')])->execute();
	}

	/**
	 * Function to check if record need a new number of sequence.
	 *
	 * @return bool
	 */
	public function isNewSequence(): bool
	{
		return $this->getRecord()->isNew() ||
			($this->getRecord()->getPreviousValue($this->getPicklistName()) !== false && !$this->getRecord()->isEmpty($this->getPicklistName()) && $this->getPicklistValue($this->getPicklistName()) !== $this->getPicklistValue($this->getPicklistName(), $this->getRecord()->getPreviousValue($this->getPicklistName())));
	}

	/**
	 * Returns prefix of picklist.
	 *
	 * @param string      $piclistName
	 * @param string|null $recordValue
	 *
	 * @return string
	 */
	private function getPicklistValue(string $piclistName, ?string $recordValue = null): string
	{
		$values = Picklist::getValues($piclistName);
		if (!isset($recordValue)) {
			$recordValue = $this->getRecord()->get($piclistName);
		}
		foreach ($values as $value) {
			if ($recordValue === $value[$piclistName]) {
				return $value['prefix'] ?? '';
			}
		}
		return '';
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
				$queryGenerator->setFields([$picklistName, $fieldModel->getFieldName(), 'id']);
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
					while ($recordinfo = $dataReader->read()) {
						$this->setRecord($moduleModel->getRecordFromArray($recordinfo));
						$picklistValue = $this->getPicklistValue($picklistName, $recordinfo[$picklistName]);
						$seq = 0;
						if ($picklistValue && isset($sequences[$picklistValue])) {
							$seq = $sequences[$picklistValue]++;
						} elseif ($picklistValue) {
							$sequences[$picklistValue] = 1;
							$seq = $sequences[$picklistValue]++;
						} else {
							$seq = $sequenceNumber++;
						}
						$dbCommand->update($fieldTable, [$fieldColumn => $this->parseNumber($seq)], [$moduleModel->getEntityInstance()->table_index => $recordinfo['id']])
							->execute();
						$returninfo['updatedrecords']++;
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
	 * @param null|int $time
	 *
	 * @return false|string
	 */
	public static function date($format, $time = null)
	{
		if ($time === null) {
			$time = time();
		}
		return date($format, $time);
	}

	/**
	 * Get sequence number that should be saved.
	 *
	 * @param string $resetSequence one character
	 */
	public static function getSequenceNumber($resetSequence)
	{
		switch ($resetSequence) {
			case 'Y':
				return static::date('Y');
			case 'M':
				return static::date('Ym'); // with year because 2016-10 (10) === 2017-10 (10) and number will be incremented but should be set to 1 (new year)
			case 'D':
				return static::date('Ymd'); // same as above because od 2016-10-03 (03) === 2016-11-03 (03)
			default:
				return '';
		}
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
				'cur_sequence' => $this->get('cur_sequence')
			])->execute();
		} else {
			return $dbCommand->update('vtiger_modentity_num', [
				'cur_id' => $this->get('cur_id'),
				'prefix' => $this->get('prefix'),
				'leading_zeros' => $this->get('leading_zeros'),
				'postfix' => $this->get('postfix'),
				'reset_sequence' => $this->get('reset_sequence'),
				'cur_sequence' => $this->get('cur_sequence')],
				['tabid' => $this->get('tabid')])
				->execute();
		}
	}
}
