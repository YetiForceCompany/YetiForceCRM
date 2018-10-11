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
class RecordNumber
{
	/**
	 * Function that checks if a module has serial number configuration.
	 *
	 * @param int $tabId
	 *
	 * @return bool
	 */
	public static function isModuleSequenceConfigured($tabId)
	{
		if (!is_numeric($tabId)) {
			$tabId = \App\Module::getModuleId($tabId);
		}
		$exist = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => $tabId])->exists();
		if ($exist) {
			return true;
		}
		return false;
	}

	/**
	 * Function to set number sequence of recoords for module.
	 *
	 * @param mixed       $tabId
	 * @param string      $prefix
	 * @param int         $no
	 * @param string      $postfix
	 * @param int         $leadingZeros
	 * @param null|string $resetSequence 'Y'-Year, 'M'-Month, 'D'-Day
	 * @param string      $curSequence   '201804' for example for M reset sequence
	 *
	 * @return bool
	 */
	public static function setNumber($tabId, $prefix = '', $no = '', $postfix = '', $leadingZeros = 0, $resetSequence = null, $curSequence = '')
	{
		if ($no != '') {
			$db = \App\Db::getInstance();
			if (!is_numeric($tabId)) {
				$tabId = \App\Module::getModuleId($tabId);
			}
			$current = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => $tabId])->one();
			if (!$current['cur_id']) {
				return $db->createCommand()->insert('vtiger_modentity_num', [
					'tabid' => $tabId,
					'prefix' => $prefix,
					'leading_zeros' => $leadingZeros,
					'postfix' => $postfix,
					'start_id' => $no,
					'cur_id' => $no,
					'reset_sequence' => $resetSequence,
					'cur_sequence' => $curSequence
				])->execute();
			} else {
				return $db->createCommand()
					->update('vtiger_modentity_num', [
						'cur_id' => $no,
						'prefix' => $prefix,
						'leading_zeros' => $leadingZeros,
						'postfix' => $postfix,
						'reset_sequence' => $resetSequence,
						'cur_sequence' => $curSequence],
						['tabid' => $tabId])
						->execute();
			}
		}
	}

	/**
	 * Function that gets the next sequence number of a record.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return string
	 */
	public static function incrementNumber($recordModel)
	{
		$moduleId = $recordModel->getModule()->getId();
		$row = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => $moduleId])->one();
		$actualSequence = static::getSequenceNumber($row['reset_sequence']);
		if ($row['reset_sequence'] && $row['cur_sequence'] !== $actualSequence) {
			$row['cur_id'] = 1;
		}
		$fullPrefix = static::parse($row['prefix'], $row['cur_id'], $row['postfix'], $row['leading_zeros'], $recordModel);
		$strip = \strlen($row['cur_id']) - \strlen($row['cur_id'] + 1);
		if ($strip < 0) {
			$strip = 0;
		}
		$temp = str_repeat('0', $strip);
		$reqNo = $temp . ($row['cur_id'] + 1);
		\App\Db::getInstance()->createCommand()->update('vtiger_modentity_num', ['cur_id' => $reqNo, 'cur_sequence' => $actualSequence], ['tabid' => $moduleId])->execute();
		return \App\Purifier::decodeHtml($fullPrefix);
	}

	/**
	 * Get sequence number that should be saved.
	 *
	 * @param string $resetSequence one character
	 *
	 * @return string|date
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
	 * Converts record numbering variables to values.
	 *
	 * @see Important: When you add new parameter in this function you also must add it in Email::findRecordNumber()
	 *
	 * @param string                     $prefix
	 * @param string                     $number
	 * @param string                     $postfix
	 * @param int                        $leadingZeros
	 * @param \Vtiger_Record_Model|false $recordModel
	 *
	 * @return string
	 */
	public static function parse($prefix, $number, $postfix, $leadingZeros, $recordModel = false)
	{
		$number = str_pad((string) $number, $leadingZeros, '0', STR_PAD_LEFT);
		$number = $prefix . $number . $postfix;
		if ($recordModel) {
			$textParser = \App\TextParser::getInstanceByModel($recordModel);
			$textParser->setContent($number);
			$textParser->parse();
			$number = $textParser->getContent();
		}
		return str_replace(
			['{{YYYY}}', '{{YY}}', '{{MM}}', '{{M}}', '{{DD}}', '{{D}}', '{', '}'],
			[static::date('Y'), static::date('y'), static::date('m'), static::date('n'), static::date('d'), static::date('j'), '', ''],
			$number
		);
	}

	/**
	 * Function updates module number.
	 *
	 * @param int    $curId
	 * @param string $curSequence
	 * @param int    $tabId
	 */
	public static function updateNumber($curId, $curSequence, $tabId)
	{
		return \App\Db::getInstance()->createCommand()
			->update('vtiger_modentity_num', ['cur_id' => $curId, 'cur_sequence' => $curSequence], ['tabid' => $tabId])
			->execute();
	}

	/**
	 * Function returns information about module numbering.
	 *
	 * @param int $tabId
	 *
	 * @return array
	 */
	public static function getNumber($tabId)
	{
		if (is_string($tabId)) {
			$tabId = \App\Module::getModuleId($tabId);
		}
		$row = (new \App\Db\Query())->from('vtiger_modentity_num')->where(['tabid' => $tabId])->one();
		return [
			'prefix' => $row['prefix'],
			'leading_zeros' => $row['leading_zeros'],
			'sequenceNumber' => $row['cur_id'],
			'postfix' => $row['postfix'],
			'reset_sequence' => $row['reset_sequence'],
			'cur_sequence' => $row['cur_sequence'],
			'number' => self::parse($row['prefix'], $row['cur_id'], $row['postfix'], $row['leading_zeros']),
		];
	}
}
