<?php

namespace App\Fields;

/**
 * Record number class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
	public static function setNumber($tabId, $prefix = '', $no = '', $postfix = '')
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
				])->execute();

				return true;
			} else {
				if ($no < $currentId) {
					return false;
				} else {
					$db->createCommand()
						->update('vtiger_modentity_num', ['cur_id' => $no, 'prefix' => $prefix, 'postfix' => $postfix], ['tabid' => $tabId])
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
		$row = (new \App\Db\Query())->select(['cur_id', 'prefix', 'postfix'])->from('vtiger_modentity_num')->where(['tabid' => $moduleId])->one();
		$prefix = $row['prefix'];
		$postfix = $row['postfix'];
		$curId = $row['cur_id'];
		$fullPrefix = self::parse($prefix . $curId . $postfix);
		$strip = strlen($curId) - strlen($curId + 1);
		if ($strip < 0) {
			$strip = 0;
		}
		$temp = str_repeat('0', $strip);
		$reqNo = $temp . ($curId + 1);
		\App\Db::getInstance()->createCommand()->update('vtiger_modentity_num', ['cur_id' => $reqNo], ['cur_id' => $curId, 'tabid' => $moduleId])->execute();

		return \App\Purifier::decodeHtml($fullPrefix);
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
	public static function parse($content)
	{
		return str_replace(['{{YYYY}}', '{{YY}}', '{{MM}}', '{{M}}', '{{DD}}', '{{D}}'], [date('Y'), date('y'), date('m'), date('n'), date('d'), date('j')], $content);
	}

	/**
	 * Function updates module number.
	 *
	 * @param int $curId
	 * @param int $tabId
	 */
	public static function updateNumber($curId, $tabId)
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_modentity_num', ['cur_id' => $curId], ['tabid' => $tabId])
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
		$row = (new \App\Db\Query())->select(['cur_id', 'prefix', 'postfix'])->from('vtiger_modentity_num')->where(['tabid' => $tabId])->one();

		$number = [
			'prefix' => $row['prefix'],
			'sequenceNumber' => $row['cur_id'],
			'postfix' => $row['postfix'],
			'number' => self::parse($row['prefix'] . $row['cur_id'] . $row['postfix']),
		];
		if ($cache) {
			self::$numberCache[$tabId] = $number;
		}

		return $number;
	}
}
