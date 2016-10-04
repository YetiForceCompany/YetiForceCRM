<?php namespace includes\fields;

/**
 * Record number class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class RecordNumber
{

	/**
	 * Function that checks if a module has serial number configuration.
	 * @param int $tabId
	 * @return boolean
	 */
	public static function isModuleSequenceConfigured($tabId)
	{
		$db = \PearDatabase::getInstance();
		if (!is_numeric($tabId)) {
			$tabId = \includes\Modules::getModuleId($tabId);
		}
		$result = $db->pquery('SELECT 1 FROM vtiger_modentity_num WHERE tabid = ?', [$tabId]);
		if ($result && $db->num_rows($result) > 0) {
			return true;
		}
		return false;
	}

	/**
	 * Function to set number sequence of recoords for module
	 * @param mixed $tabId
	 * @param string $prefix
	 * @param int $no
	 * @param string $postfix
	 * @return boolean
	 */
	public static function setNumber($tabId, $prefix = '', $no = '', $postfix = '')
	{
		if ($no != '') {
			$db = \PearDatabase::getInstance();
			if (!is_numeric($tabId)) {
				$tabId = \includes\Modules::getModuleId($tabId);
			}
			$query = 'SELECT cur_id FROM vtiger_modentity_num WHERE tabid = ?';
			$check = $db->pquery($query, [$tabId]);
			$numRows = $db->getRowCount($check);
			if ($numRows == 0) {
				$db->insert('vtiger_modentity_num', [
					'tabid' => $tabId,
					'prefix' => $prefix,
					'postfix' => $postfix,
					'start_id' => $no,
					'cur_id' => $no,
				]);
				return true;
			} else {
				if ($no < $db->getSingleValue($check)) {
					return false;
				} else {
					$db->update('vtiger_modentity_num', ['cur_id' => $no, 'prefix' => $prefix, 'postfix' => $postfix], 'tabid = ?', [$tabId]);
					return true;
				}
			}
		}
	}

	/**
	 * Function that gets the next sequence number of a record
	 * @param int $moduleId Number id for module
	 * @return string
	 */
	public static function incrementNumber($moduleId)
	{
		$db = \PearDatabase::getInstance();
		//when we save new invoice we will increment the invoice id and write
		$result = $db->pquery('SELECT cur_id, prefix, postfix FROM vtiger_modentity_num WHERE tabid = ?', [$moduleId]);
		$row = $db->getRow($result);

		$prefix = $row['prefix'];
		$postfix = $row['postfix'];
		$curid = $row['cur_id'];
		$fullPrefix = self::parse($prefix . $curid . $postfix);
		$strip = strlen($curid) - strlen($curid + 1);
		if ($strip < 0) {
			$strip = 0;
		}
		$temp = str_repeat('0', $strip);
		$reqNo = $temp . ($curid + 1);
		$db->update('vtiger_modentity_num', ['cur_id' => $reqNo], 'cur_id = ? && tabid = ?', [$curid, $moduleId]);
		return decode_html($fullPrefix);
	}

	/**
	 * Converts record numbering variables to values
	 * @param string $content
	 * @return string
	 */
	public static function parse($content)
	{
		$content = str_replace('{{YYYY}}', date('Y'), $content);
		$content = str_replace('{{YY}}', date('y'), $content);
		$content = str_replace('{{MM}}', date('m'), $content);
		$content = str_replace('{{M}}', date('n'), $content);
		$content = str_replace('{{DD}}', date('d'), $content);
		$content = str_replace('{{D}}', date('j'), $content);
		return $content;
	}

	public static function updateNumber($curId, $tabId)
	{
		$db = \PearDatabase::getInstance();
		$db->update('vtiger_modentity_num', ['cur_id' => $curId], 'tabid = ?', [$tabId]);
	}

	protected static $numberCache = [];

	public static function getNumber($tabId, $cache = true)
	{
		if (isset(self::$numberCache[$tabId]) && $cache) {
			return self::$numberCache[$tabId];
		}
		if (is_string($tabId)) {
			$tabId = \includes\Modules::getModuleId($tabId);
		}
		$adb = \PearDatabase::getInstance();
		$result = $adb->pquery('SELECT cur_id, prefix, postfix FROM vtiger_modentity_num WHERE tabid = ? ', [$tabId]);
		$row = $adb->getRow($result);

		$number = [
			'prefix' => $row['prefix'],
			'sequenceNumber' => $row['cur_id'],
			'postfix' => $row['postfix'],
			'number' => self::parse($row['prefix'] . $row['cur_id'] . $row['postfix'])
		];
		if ($cache) {
			self::$numberCache[$tabId] = $number;
		}
		return $number;
	}
}
