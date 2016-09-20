<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class OSSMailTemplates_Record_Model extends Vtiger_Record_Model
{

	public function getTempleteList($module)
	{
		$db = PearDatabase::getInstance();
		$sql = "SELECT * FROM vtiger_ossmailtemplates WHERE oss_module_list = ?";
		$result = $db->pquery($sql, [$module]);
		$list = [];
		while ($row = $db->fetch_array($result)) {
			$list[$row['ossmailtemplatesid']] = $row;
		}
		return $list;
	}

	public function getTemplete($id = false, $sysname = false)
	{
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM vtiger_ossmailtemplates WHERE ';
		if ($sysname) {
			$id = $sysname;
			$sql .= 'sysname = ?';
		} else {
			$sql .= 'ossmailtemplatesid = ?';
		}
		$result = $db->pquery($sql, [$id]);
		$row = $db->fetch_array($result);
		$output = [
			'subject' => $row['subject'],
			'content' => $row['content'],
		];
		$query = 'SELECT notesid FROM vtiger_senotesrel '
			. 'INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_senotesrel.notesid '
			. 'WHERE vtiger_crmentity.deleted = 0 && vtiger_senotesrel.crmid = ?';
		$res = $db->pquery($query, [$id]);
		$aid = [];
		while ($notesid = $db->getSingleValue($res)) {
			$aid[] = $notesid;
		}
		if (count($aid) > 0) {
			$output['attachments'] = ['ids' => $aid];
		}
		return $output;
	}

	public function sendMailFromTemplate($data)
	{
		require_once('modules/Emails/mail.php');

		$id = key_exists('id', $data) ? $data['id'] : false;
		$sysname = key_exists('sysname', $data) ? $data['sysname'] : false;
		$output = self::getTemplete($id, $sysname);
		$logo = 0;
		$request = array();
		$entityId = $data['record'];
		$module = $data['module'];
		$toEmail = $data['to_email'];
		if ($entityId && $module) {
			$data['Model'] = Vtiger_Record_Model::getInstanceById($entityId, $module);
		} elseif ($data['request']) {
			$data['Model'] = $data['request'];
		}
		if ($data['cc']) {
			$cc = $data['cc'];
		}
		if ($data['bcc']) {
			$bcc = $data['bcc'];
		}
		if ($data['attachment']) {
			$attachment = 'all';
			$emailid = $data['attachment'];
		}

		if (@strpos($output['content'], '#s#LogoImage#sEnd#') !== false)
			$logo = 1;

		$translatedLanguage = '';
		if ($data['notifilanguage'] != '') {
			$translatedLanguage = vglobal('translated_language');
			vglobal('translated_language', $data['notifilanguage']);
		}
		$this->findVar($output['content'], 0, $entityId, $module, 'a', $data);
		$this->findVar($output['content'], 0, $entityId, $module, 'b', $data);
		$this->findVar($output['content'], 0, $entityId, $module, 'c', $data);
		$this->findVar($output['content'], 0, $entityId, $module, 'd', $data);
		$this->findVar($output['content'], 0, $entityId, $module, 's', $data);
		$this->findVar($output['content'], 0, $entityId, $module, 't', $data);

		$this->findVar($output['subject'], 0, $entityId, $module, 'a', $data);
		$this->findVar($output['subject'], 0, $entityId, $module, 'b', $data);
		$this->findVar($output['subject'], 0, $entityId, $module, 'c', $data);
		$this->findVar($output['subject'], 0, $entityId, $module, 'd', $data);
		$this->findVar($output['subject'], 0, $entityId, $module, 's', $data);
		$this->findVar($output['subject'], 0, $entityId, $module, 't', $data);

		vglobal('translated_language', $translatedLanguage);
		$mailStatus = send_mail($module, $toEmail, '', '', $output['subject'], $output['content'], $cc, $bcc, $attachment, $emailid, $logo, false, $data['attachment_src']);
		return $mailStatus;
	}

	public function findVar(&$tpl, $offset, $recordId, $module, $type, $request)
	{
		$startType = "#" . $type . '#';
		$endType = '#' . $type . 'End#';
		$startTypeLength = strlen($startType);
		$start = @strpos($tpl, $startType, $offset);
		if ($start !== false) {
			$start = (int) $start;
			$end = (int) strpos($tpl, $endType, $start);
			$fieldId = substr($tpl, $start + $startTypeLength, $end - $start - $startTypeLength);
			$positionLength = (int) ($end - $start - $startTypeLength);
			if ($fieldId != '') {
				switch ($startType) {
					case '#a#':
						$tpl = $this->replaceVar($fieldId, $tpl, $recordId, $module, $start, $positionLength, strlen($startType . $endType));
						break;
					case '#b#':
						$IDs = explode('||', $fieldId);
						$tpl = $this->replaceLabel($IDs[0], $tpl, $module, $start, $positionLength, strlen($startType . $endType), true);
						break;
					case '#c#':
						$tpl = $this->replaceRelVar($fieldId, $tpl, $recordId, $module, $start, $positionLength, strlen($startType . $endType));
						break;
					case '#d#':
						$tpl = $this->replaceLabel($fieldId, $tpl, $module, $start, $positionLength, strlen($startType . $endType));
						break;
					case '#s#':
						$tpl = $this->replaceSpecialFunction($fieldId, $tpl, $recordId, $module, $start, $positionLength, strlen($startType . $endType), $request);
						break;
					case '#t#':
						$tpl = $this->replaceTranslation($fieldId, $tpl, $recordId, $module, $start, $positionLength, strlen($startType . $endType), $request);
						break;
				}
			}
			$this->findVar($tpl, $start + 1, $recordId, $module, $type, $request);
		}
		$startType = "%23" . $type . '%23';
		$endType = '%23' . $type . 'End%23';
		$startTypeLength = strlen($startType);
		$start = @strpos($tpl, $startType, $offset);
		if ($start !== false) {
			$start = (int) $start;
			$end = (int) strpos($tpl, $endType, $start);
			$fieldId = substr($tpl, $start + $startTypeLength, $end - $start - $startTypeLength);
			$positionLength = (int) ($end - $start - $startTypeLength);
			if ($fieldId != '') {
				switch ($startType) {
					case '%23a%23':
						$tpl = $this->replaceVar($fieldId, $tpl, $recordId, $module, $start, $positionLength, strlen($startType . $endType));
						break;
					case '%23b%23':
						$IDs = explode('||', $fieldId);
						$tpl = $this->replaceLabel($IDs[0], $tpl, $module, $start, $positionLength, strlen($startType . $endType), true);
						break;
					case '%23c%23':
						$tpl = $this->replaceRelVar($fieldId, $tpl, $recordId, $module, $start, $positionLength, strlen($startType . $endType));
						break;
					case '%23d%23':
						$tpl = $this->replaceLabel($fieldId, $tpl, $module, $start, $positionLength, strlen($startType . $endType));
						break;
					case '%23s%23':
						$tpl = $this->replaceSpecialFunction($fieldId, $tpl, $recordId, $module, $start, $positionLength, strlen($startType . $endType), $request);
						break;
					case '%23t%23':
						$tpl = $this->replaceTranslation($fieldId, $tpl, $recordId, $module, $start, $positionLength, strlen($startType . $endType), $request);
						break;
				}
			}
			$this->findVar($tpl, $start + 1, $recordId, $module, $type, $request);
		}
	}

	public function replaceVar($fieldId, $tpl, $recordId, $module, $start, $positionLength, $allLength)
	{
		$db = PearDatabase::getInstance();
		$getFieldInfoSql = "SELECT * FROM vtiger_field WHERE fieldid = $fieldId";
		$getFieldInfoResult = $db->query($getFieldInfoSql, true);

		if ($db->num_rows($getFieldInfoResult) == 0)
			return $tpl;

		$fieldTab = $db->query_result_raw($getFieldInfoResult, 0, 'tablename');
		$fieldColumnName = $db->query_result_raw($getFieldInfoResult, 0, 'columnname');
		$tabid = $db->query_result_raw($getFieldInfoResult, 0, 'tabid');
		$uitype = $db->query_result_raw($getFieldInfoResult, 0, 'uitype');
		$moduleNameResult = $db->query("SELECT name FROM vtiger_tab WHERE tabid = $tabid", true);
		$fieldModule = $db->query_result_raw($moduleNameResult, 0, 'name');

		if ($fieldModule != $module && $module != 'Calendar') {
			return $tpl;
		}
		if ($module == 'Calendar' || $module == 'Events') {
			vimport("~~modules/Calendar/Activity.php");
			$module = 'Activity';
		} else {
			vimport("~~modules/$module/$module.php");
		}
		$modObj = new $module();
		$primaryKey = $modObj->tab_name_index[$fieldTab];
		$getValueSql = "SELECT $fieldColumnName FROM $fieldTab WHERE $primaryKey = $recordId";
		$getValueResult = $db->query($getValueSql, true);
		$finalValue = $db->query_result_raw($getValueResult, 0, $fieldColumnName);
		if ($uitype == 10 || $uitype == 51 || $uitype == 73 || $uitype == 66 || $uitype == 57) {
			$finalValue = vtlib\Functions::getCRMRecordLabel($finalValue);
		} elseif ($uitype == 15 || $uitype == 16) {
			$finalValue = vtranslate($finalValue, $module);
		} elseif ($uitype == 53 || $uitype == 52) {
			$finalValue = vtlib\Functions::getOwnerRecordLabel($finalValue);
		} elseif ($uitype == 56) {
			if (0 == $finalValue) {
				$finalValue = vtranslate('LBL_NO');
			} else {
				$finalValue = vtranslate('LBL_YES');
			}
		}
		$tpl = substr_replace($tpl, $finalValue, $start, $allLength + $positionLength);
		return $tpl;
	}

	//Funkcja podmieniająca pola z modułów powiązanych - Funkcja do wymiany
	public function replaceRelVar($fieldId, $tpl, $recordId, $module, $start, $positionLength, $allLength)
	{
		$db = PearDatabase::getInstance();
		vimport("~~modules/$module/$module.php");
		$IDs = explode('||', $fieldId);
		$getFieldInfoSql = sprintf('SELECT * FROM vtiger_field WHERE fieldid = %s', $IDs[0]);
		$getFieldInfoResult = $db->query($getFieldInfoSql, true);
		$fieldTab = $db->query_result_raw($getFieldInfoResult, 0, 'tablename');
		$fieldname = $db->query_result_raw($getFieldInfoResult, 0, 'fieldname');
		$fieldColumnName = $db->query_result_raw($getFieldInfoResult, 0, 'columnname');
		$tabid = $db->query_result_raw($getFieldInfoResult, 0, 'tabid');
		$uitype = $db->query_result_raw($getFieldInfoResult, 0, 'uitype');
		$moduleNameResult = $db->pquery("SELECT name FROM vtiger_tab WHERE tabid = ?", array($tabid), true);
		$fieldModule = $db->query_result_raw($moduleNameResult, 0, 'name');
		$moduleInstance = Vtiger_Record_Model::getInstanceById($recordId, $module);
		$getFieldInfoSql2 = "SELECT * FROM vtiger_field WHERE fieldid = ?";
		$getFieldInfoResult2 = $db->pquery($getFieldInfoSql2, array($IDs[1]), true);
		$rel_id = $moduleInstance->get($db->query_result_raw($getFieldInfoResult2, 0, 'fieldname'));
		$modObj = CRMEntity::getInstance($fieldModule);
		$primaryKey = $modObj->tab_name_index[$fieldTab];
		$getValueSql = "SELECT $fieldColumnName FROM $fieldTab WHERE $primaryKey = ?;";
		$getValueResult = $db->pquery($getValueSql, array($rel_id), true);
		$finalValue = $db->query_result_raw($getValueResult, 0, $fieldColumnName);
		if ($uitype == 10 || $uitype == 51 || $uitype == 73 || $uitype == 66 || $uitype == 57) {
			$finalValue = vtlib\Functions::getCRMRecordLabel($finalValue);
		} elseif ($uitype == 15 || $uitype == 16) {
			$finalValue = vtranslate($finalValue, $module);
		} elseif ($uitype == 53 || $uitype == 52) {
			$finalValue = vtlib\Functions::getOwnerRecordLabel($finalValue);
		}
		$tpl = substr_replace($tpl, $finalValue, $start, $allLength + $positionLength);
		return $tpl;
	}

	public function replaceLabel($fieldId, $tpl, $module, $start, $positionLength, $allLength, $rel = false)
	{
		$db = PearDatabase::getInstance();
		$getFieldInfoSql = "SELECT * FROM vtiger_field WHERE fieldid = ?";
		$getFieldInfoResult = $db->pquery($getFieldInfoSql, array($fieldId), true);
		$label = $db->query_result_raw($getFieldInfoResult, 0, 'fieldlabel');
		if (!$rel) {
			$trLabel = vtranslate($label, $module);
		} else {
			$tabid = $db->query_result_raw($getFieldInfoResult, 0, 'tabid');
			$moduleNameResult = $db->pquery("SELECT name FROM vtiger_tab WHERE tabid = ?", array($tabid), true);
			$module = $db->query_result_raw($moduleNameResult, 0, 'name');
			$trLabel = vtranslate($label, $module);
		}
		$tpl = substr_replace($tpl, $trLabel, $start, $allLength + $positionLength);
		return $tpl;
	}

	public function replaceSpecialFunction($className, $tpl, $recordId, $module, $start, $positionLength, $allLength, $request)
	{
		$fullPath = 'modules' . DIRECTORY_SEPARATOR . 'OSSMailTemplates' .
			DIRECTORY_SEPARATOR . 'special_functions' . DIRECTORY_SEPARATOR . $className . '.php';
		if (file_exists($fullPath)) {
			require_once $fullPath;
			$funObj = new $className;
			if (in_array($module, $funObj->getListAllowedModule()) || in_array('all', $funObj->getListAllowedModule())) {
				$tpl = substr_replace($tpl, $funObj->process($request), $start, $allLength + $positionLength);
			}
		}
		return $tpl;
	}

	public function replaceTranslation($label, $tpl, $recordId, $module, $start, $positionLength, $allLength, $request)
	{
		$translatedLabel = vtranslate($label, $module);
		$tpl = substr_replace($tpl, $translatedLabel, $start, $allLength + $positionLength);
		return $tpl;
	}
}
