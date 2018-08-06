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
	 * @param mixed  $tabId
	 * @param string $prefix
	 * @param int    $no
	 * @param string $postfix
	 *
	 * @return bool
	 */
	public static function setNumber($tabId, $prefix = '', $no = '', $postfix = '', $resetSequence = null, $curSequence = 0)
	{
		if ($no != '') {
			$db = \App\Db::getInstance();
			if (!is_numeric($tabId)) {
				$tabId = \App\Module::getModuleId($tabId);
			}
			$currentId = (new \App\Db\Query())->select(['cur_id'])->from('vtiger_modentity_num')
				->where(['tabid' => $tabId])
				->scalar();
			if (!$currentId) {
				$db->createCommand()->insert('vtiger_modentity_num', [
					'tabid' => $tabId,
					'prefix' => $prefix,
					'postfix' => $postfix,
					'start_id' => $no,
					'cur_id' => $no,
					'reset_sequence' => $resetSequence,
					'cur_sequence' => $curSequence
				])->execute();

				return true;
			} else {
				if ($no < $currentId) {
					return false;
				} else {
					$db->createCommand()
						->update('vtiger_modentity_num', ['cur_id' => $no, 'prefix' => $prefix, 'postfix' => $postfix, 'reset_sequence' => $resetSequence, 'cur_sequence' => $curSequence], ['tabid' => $tabId])
						->execute();

					return true;
				}
			}
		}
	}

	/**
	 * Function that gets the next sequence number of a record.
	 *
	 * @param int $moduleId Number id for module
	 *
	 * @return string
	 */
	public static function incrementNumber($moduleId)
	{
		$row = (new \App\Db\Query())->select(['cur_id', 'prefix', 'postfix', 'reset_sequence', 'cur_sequence'])->from('vtiger_modentity_num')->where(['tabid' => $moduleId])->one();
		$actualSequence = static::getSequenceNumber($row['reset_sequence']);
		if ($row['cur_sequence'] !== $actualSequence) {
			$row['cur_id'] = 1;
		}
		$fullPrefix = self::parse($row['prefix'], $row['cur_id'], $row['postfix'], $row['reset_sequence']);
		$strip = strlen($row['cur_id']) - strlen($row['cur_id'] + 1);
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
	 */
	public static function getSequenceNumber($resetSequence)
	{
		switch ($resetSequence) {
			case 'Y':
				return (int) date('Y');
				break;
			case 'M':
				return (int) date('n');
				break;
			case 'D':
				return (int) date('j');
				break;
		}
	}

	/**
	 * Converts record numbering variables to values.
	 *
	 * @see Important: When you add new parameter in this function you also must add it in Email::findRecordNumber()
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function parse($prefix, $number, $postfix)
	{
		$leadingZeros = substr_count($prefix, '{{0}}');
		$prefix = str_replace('{{0}}', '', $prefix);
		$number = str_pad((string) $number, $leadingZeros, '0', STR_PAD_LEFT);
		return str_replace(['{{YYYY}}', '{{YY}}', '{{MM}}', '{{M}}', '{{DD}}', '{{D}}'], [date('Y'), date('y'), date('m'), date('n'), date('d'), date('j')], $prefix . $number . $postfix);
	}

	/**
	 * Function updates module number.
	 *
	 * @param int $curId
	 * @param int $tabId
	 */
	public static function updateNumber($curId, $curSequence, $tabId)
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_modentity_num', ['cur_id' => $curId, 'cur_sequence' => $curSequence], ['tabid' => $tabId])
			->execute();
	}

	protected static $numberCache = [];

	/**
	 * Function returns information about module numbering.
	 *
	 * @param int    $tabId
	 * @param boolen $cache
	 *
	 * @return array
	 */
	public static function getNumber($tabId, $cache = true)
	{
		if (isset(self::$numberCache[$tabId]) && $cache) {
			return self::$numberCache[$tabId];
		}
		if (is_string($tabId)) {
			$tabId = \App\Module::getModuleId($tabId);
		}
		$row = (new \App\Db\Query())->select(['cur_id', 'prefix', 'postfix', 'reset_sequence', 'cur_sequence'])->from('vtiger_modentity_num')->where(['tabid' => $tabId])->one();

		$number = [
			'prefix' => $row['prefix'],
			'sequenceNumber' => $row['cur_id'],
			'postfix' => $row['postfix'],
			'reset_sequence' => $row['reset_sequence'],
			'cur_sequence' => $row['cur_sequence'],
			'number' => self::parse($row['prefix'], $row['cur_id'], $row['postfix'], $row['reset_sequence']),
		];
		if ($cache) {
			self::$numberCache[$tabId] = $number;
		}
		return $number;
	}
}
