<?php namespace includes\fields;

/**
 * Recurd Numer class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class RecordNumber
{

	public static function setConfiguration($tabId, $req_str = '', $req_no = '', $reqPostfix = '')
	{
		if ($req_no != '') {
			$db = \PearDatabase::getInstance();
			if(is_string($tabId)){
				$tabId = \vtlib\Functions::getModuleId($tabId);
			}
			$query = 'SELECT cur_id FROM vtiger_modentity_num WHERE tabid = ? AND prefix = ? AND postfix = ?;';
			$check = $db->pquery($query, [$tabId, $req_str, $reqPostfix]);
			$numRows = $db->num_rows($check);
			if ($numRows == 0) {
				$params = [
					'tabid' => $tabId,
					'prefix' => $req_str,
					'postfix' => $reqPostfix,
					'start_id' => $req_no,
					'cur_id' => $req_no,
				];
				$db->insert('vtiger_modentity_num', $params);
				return true;
			} else if ($numRows != 0) {
				$num_check = $db->query_result($check, 0, 'cur_id');
				if ($req_no < $num_check) {
					return false;
				} else {
					$db->update('vtiger_modentity_num', ['cur_id' => $req_no], 'prefix = ? AND postfix = ? AND tabid = ?', [$req_str, $reqPostfix, $tabId]);
					return true;
				}
			}
		}
	}

	public static function setIncrementSeqNumber($moduleId)
	{
		$db = \PearDatabase::getInstance();
		//when we save new invoice we will increment the invoice id and write
		$result = $db->pquery('SELECT cur_id, prefix, postfix FROM vtiger_modentity_num WHERE tabid = ?', [$moduleId]);
		$row = $db->getRow($result);
		
		
		$prefix = $row['prefix'];
		$postfix = $row['postfix'];
		$curid = $row['cur_id'];
		$fullPrefix = \Settings_Vtiger_CustomRecordNumberingModule_Model::parseNumberingVariables($prefix . $curid . $postfix);
		$strip = strlen($curid) - strlen($curid + 1);
		if ($strip < 0) {
			$strip = 0;
		}
		$temp = str_repeat('0', $strip);
		$reqNo .= $temp . ($curid + 1);
		$db->update('vtiger_modentity_num', ['cur_id' => $reqNo], 'cur_id = ? AND tabid = ?', [$curid, $moduleId]);
		return decode_html($fullPrefix);
	}
	public static function updateSeqNumber($curId, $tabId){
		$db = \PearDatabase::getInstance();
		$db->update('vtiger_modentity_num', ['cur_id' => $curId], 'tabid = ?', [$tabId]);		
	}
}
