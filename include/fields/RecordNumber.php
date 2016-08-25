<?php namespace includes\fields;

/**
 * Recurd Numer class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class RecordNumber
{

	public static function setNumber($tabId, $prefix = '', $no = '', $postfix = '')
	{
		if ($no != '') {
			$db = \PearDatabase::getInstance();
			if (is_string($tabId)) {
				$tabId = \vtlib\Functions::getModuleId($tabId);
			}
			$query = 'SELECT cur_id FROM vtiger_modentity_num WHERE tabid = ? AND prefix = ? AND postfix = ?;';
			$check = $db->pquery($query, [$tabId, $prefix, $postfix]);
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
					$db->update('vtiger_modentity_num', ['cur_id' => $no], 'prefix = ? AND postfix = ? AND tabid = ?', [$prefix, $postfix, $tabId]);
					return true;
				}
			}
		}
	}

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
		$db->update('vtiger_modentity_num', ['cur_id' => $reqNo], 'cur_id = ? AND tabid = ?', [$curid, $moduleId]);
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

	public static function getNumber($tabId)
	{
		if (is_string($tabId)) {
			$tabId = \vtlib\Functions::getModuleId($tabId);
		}
		$adb = \PearDatabase::getInstance();
		$result = $adb->pquery('SELECT cur_id, prefix, postfix FROM vtiger_modentity_num WHERE tabid = ? ', [$tabId]);
		$row = $adb->getRow($result);
		return [
			'prefix' => $row['prefix'],
			'sequenceNumber' => $row['cur_id'],
			'postfix' => $row['postfix'],
			'number' => $row['prefix'] . $row['cur_id'] . $row['postfix']
		];
	}
}
