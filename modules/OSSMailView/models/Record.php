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

class OSSMailView_Record_Model extends Vtiger_Record_Model
{

	protected $modules_email_actions_widgets = [];

	function __construct()
	{
		$this->modules_email_actions_widgets['Accounts'] = true;
		$this->modules_email_actions_widgets['Contacts'] = true;
		$this->modules_email_actions_widgets['Leads'] = true;
		$this->modules_email_actions_widgets['HelpDesk'] = true;
		$this->modules_email_actions_widgets['Potentials'] = true;
		$this->modules_email_actions_widgets['Project'] = true;
		parent::__construct();
	}

	function get($key)
	{
		$value = parent::get($key);
		if ($key === 'content' && $_REQUEST['view'] == 'Detail') {
			return Vtiger_Functions::removeHtmlTags(array('link', 'style', 'a', 'img', 'script', 'base'), decode_html($value));
		}
		if ($key === 'uid' || $key === 'content') {
			return decode_html($value);
		}
		return $value;
	}

	public function isWidgetEnabled($module)
	{
		$widgets = $this->modules_email_actions_widgets;
		if ($widgets[$module]) {
			return true;
		}
		return false;
	}

	public function showEmailsList($srecord, $smodule, $config, $type, $filter = 'All')
	{
		$return = [];
		$adb = PearDatabase::getInstance();
		$widgets = $this->modules_email_actions_widgets;
		$queryParams = [];
		if ($widgets[$smodule]) {
			$ids = [];
			$relatedID = [];
			if ($filter == 'All' || $filter == 'Contacts') {
				$result = $adb->pquery('SELECT vtiger_contactdetails.contactid FROM vtiger_contactdetails '
					. 'INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid '
					. 'WHERE vtiger_contactdetails.parentid = ? AND vtiger_crmentity.deleted = ?', [$srecord, 0]);
				while ($row = $adb->fetch_array($result)) {
					$relatedID[] = $row['contactid'];
				}
			}
			if ($filter != 'Contacts') {
				$relatedID[] = $srecord;
			}
			$query = 'SELECT ossmailviewid FROM vtiger_ossmailview_relation WHERE crmid IN(' . implode(',', $relatedID) . ') AND `deleted` = ? ORDER BY `date` DESC';
			$result = $adb->pquery($query, [0]);
			
			while ($row = $adb->fetch_array($result)) {
				$ids[] = $row['ossmailviewid'];
			}
			if (count($ids) == 0) {
				return [];
			}
			$queryParams[] = $ids;
			if ($type != 'All') {
				$ifwhere = ' AND type = ?';
				$queryParams[] = $type;
			}
			$query = 'SELECT vtiger_ossmailview.* FROM vtiger_ossmailview INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossmailview.ossmailviewid';
			$query .= ' WHERE ossmailviewid IN (' . generateQuestionMarks($ids) . ')' . $ifwhere;
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$moduleName = 'OSSMailView';
			$instance = CRMEntity::getInstance($moduleName);
			$securityParameter = $instance->getUserAccessConditionsQuerySR($moduleName, $currentUser);
			if ($securityParameter != '')
				$query .= $securityParameter;
			$query .= ' ORDER BY ossmailviewid DESC';
			if($config['widget_limit'] != ''){
				$query .= ' LIMIT ' . $config['widget_limit'];
			}

			$result = $adb->pquery($query, $queryParams, true);

			while ($row = $adb->fetch_array($result)) {
				$from = $this->findRecordsById($row['from_id']);
				$from = ($from && $from != '') ? $from : $row['from_email'];
				$to = $this->findRecordsById($row['to_id']);
				$to = ($to && $to != '') ? $to : $row['to_email'];
				$content = Vtiger_Functions::removeHtmlTags(['link', 'style', 'a', 'img', 'script', 'base'], decode_html($row['content']));
				$return[] = [
					'id' => $row['ossmailviewid'],
					'date' => $row['date'],
					'firstLetter' => strtoupper(Vtiger_Functions::textLength(trim(strip_tags($from)), 1, false)),
					'subject' => '<a href="index.php?module=OSSMailView&view=preview&record=' . $row['ossmailviewid'] . '" target="' . $config['target'] . '"> ' . $row['subject'] . '</a>',
					'attachments' => $row['attachments_exist'],
					'from' => $from,
					'to' => $to,
					'url' => 'index.php?module=OSSMailView&view=preview&record=' . $row['ossmailviewid'],
					'type' => $row['type'],
					'teaser' => Vtiger_Functions::textLength(trim(preg_replace('/[ \t]+/', ' ', strip_tags($content))), 100),
					'body' => $content,
				];
			}
		}
		return $return;
	}

	public function findRecordsById($ids)
	{
		$return = false;
		if (!empty($ids) && $ids != '0') {
			$recordModelMailScanner = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
			$config = $recordModelMailScanner->getConfig('email_list');
			if (strpos($ids, ',')) {
				$idsArray = explode(",", $ids);
			} else {
				$idsArray[0] = $ids;
			}
			foreach ($idsArray as $id) {
				$module = Vtiger_Functions::getCRMRecordType($id);
				$label = Vtiger_Functions::getCRMRecordLabel($id);
				$return .= '<a href="index.php?module=' . $module . '&view=Detail&record=' . $id . '" target="' . $config['target'] . '"> ' . $label . '</a>,';
			}
		}
		return trim($return, ',');
	}

	public function findCrmRecordsByMessage_id($params, $metod)
	{
		$adb = PearDatabase::getInstance();
		$id = FALSE;
		$return = [];
		if (isset($params['crmid']['id'])) {
			$id = $params['crmid']['id'];
		} else {
			$result = $adb->pquery('SELECT ossmailviewid FROM vtiger_ossmailview WHERE id = ? AND mbox = ?', [$params['uid'], $params['folder']]);
			if ($adb->num_rows($result) > 0) {
				$id = $adb->query_result_raw($result, 0, 'ossmailviewid');
			}
		}
		if ($id) {
			if ($metod != 'all') {
				
			}
			$result = $adb->pquery('SELECT crmid FROM vtiger_ossmailview_relation WHERE ossmailviewid = ? AND `deleted` = ? ', [$id, 0]);
			for ($i = 0; $i < $adb->num_rows($result); $i++) {
				$crmid = $adb->query_result($result, $i, 'crmid');
				$resultSetype = $adb->pquery('SELECT setype,label FROM vtiger_crmentity WHERE crmid=? AND deleted=?', [$crmid, 0]);
				if ($adb->num_rows($resultSetype) == 1) {
					$module = $adb->query_result($resultSetype, 0, 'setype');
					$label = $adb->query_result($resultSetype, 0, 'label');
					$return[$module]['record'] = ['crmid' => $crmid, 'module' => $module, 'label' => $label];
					$return[$module]['rows'][] = ['crmid' => $crmid, 'module' => $module, 'label' => $label];
				}
			}
		}
		return $return;
	}

	public function findCrm($text)
	{
		$limit = 45;
		$count = strlen($text);
		if ($count >= $limit) {
			$limit_text = substr($text, 0, $limit);
			$txt = $limit_text . "...";
		} else {
			$txt = $text;
		}
		return $txt;
	}

	public function findEmail($id, $module)
	{
		if (!isRecordExists($id))
			return false;
		$returnEmail = '';
		if (strcmp($module, 'HelpDesk') != 0 && strcmp($module, 'Potentials') != 0 && strcmp($module, 'Project') != 0) {
			$polaEmail = OSSMailScanner_Record_Model::getEmailSearch($module);
			if (count($polaEmail) > 0) {
				$recordModel = Vtiger_Record_Model::getInstanceById($id, $module);
				foreach ($polaEmail as $em) {
					$email = $recordModel->get($em[2]);
					if (!empty($email)) {
						$returnEmail = $email;
					}
				}
			}
		} else {
			$kontrahentId = '';
			$kontaktId = '';
			if (strcmp($module, 'HelpDesk') == 0) {
				$helpdeskRecord = Vtiger_Record_Model::getInstanceById($id, $module);
				$kontrahentId = $helpdeskRecord->get('parent_id');
				$kontaktId = $helpdeskRecord->get('contact_id');
			} else if (strcmp($module, 'Potentials') == 0) {
				$helpdeskRecord = Vtiger_Record_Model::getInstanceById($id, $module);
				$kontrahentId = $helpdeskRecord->get('related_to');
			} else if (strcmp($module, 'Project') == 0) {
				$helpdeskRecord = Vtiger_Record_Model::getInstanceById($id, $module);
				$kontrahentId = $helpdeskRecord->get('linktoaccountscontacts');
			}
			// czy kontrahent istnieje
			if (isRecordExists($kontrahentId)) {
				$nazwaModulu = Vtiger_Functions::getCRMRecordType($kontrahentId);
				$returnEmail = $this->findEmail($kontrahentId, $nazwaModulu);
			}
			if (isRecordExists($kontaktId)) {
				$nazwaModulu = Vtiger_Functions::getCRMRecordType($kontaktId);
				$returnEmail = $this->findEmail($kontaktId, $nazwaModulu);
			}
		}
		return $returnEmail;
	}

	public function delete_rel($recordId)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT * FROM vtiger_ossmailview_files WHERE ossmailviewid = ? ", array($recordId), true);
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			$adb->pquery("UPDATE vtiger_crmentity SET deleted = '1' WHERE crmid = ?", array($row['documentsid']), true);
			$adb->pquery("UPDATE vtiger_crmentity SET deleted = '1' WHERE crmid = ?; ", array($row['attachmentsid']), true);
		}
	}

	public function bindAllRecords()
	{
		$adb = PearDatabase::getInstance();
		$this->addLog('Action_Bind', 'all');
		$adb->query("UPDATE vtiger_ossmailview SET `verify` = '1'; ", true);
	}

	public function bindSelectedRecords($selectedIds)
	{
		$adb = PearDatabase::getInstance();
		$this->addLog('Action_Bind', count($selectedIds));
		$selectedIdsSql = implode(",", $selectedIds);
		$adb->pquery("UPDATE vtiger_ossmailview SET `verify` = '1' where ossmailviewid in (?); ", array($selectedIdsSql), true);
	}

	public function getMailType()
	{
		return array(2 => 'Internal', 0 => 'Sent', 1 => 'Received');
	}

	public function ChangeTypeAllRecords($mail_type)
	{
		$MailType = $this->getMailType();
		$adb = PearDatabase::getInstance();
		$this->addLog('Action_ChangeType', 'all');
		$adb->pquery("UPDATE vtiger_ossmailview SET `ossmailview_sendtype` = ?, `type` = ?;", array($MailType[$mail_type], $mail_type), true);
	}

	public function ChangeTypeSelectedRecords($selectedIds, $mail_type)
	{
		$adb = PearDatabase::getInstance();
		$MailType = $this->getMailType();
		$this->addLog('Action_ChangeType', count($selectedIds));
		$selectedIdsSql = implode(",", $selectedIds);
		$adb->pquery("UPDATE vtiger_ossmailview SET `ossmailview_sendtype` = ?, `type` = ? where ossmailviewid in (?);", array($MailType[$mail_type], $mail_type, $selectedIdsSql), true);
	}

	public function addLog($action, $info)
	{
		$adb = PearDatabase::getInstance();
		$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
		$adb->pquery("INSERT INTO vtiger_ossmails_logs (`action`, `info`, `user`) VALUES (?, ?, ?); ", array($action, $info, $user_id), true);
	}

	public function getMailsQuery($recordId, $moduleName)
	{
		$usersSqlFullName = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$sql = "SELECT vtiger_crmentity.*, vtiger_ossmailview.*, CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $usersSqlFullName ELSE vtiger_groups.groupname END AS user_name 
			FROM vtiger_ossmailview 
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossmailview.ossmailviewid 
			INNER JOIN vtiger_ossmailviewcf ON vtiger_ossmailviewcf.ossmailviewid = vtiger_ossmailview.ossmailviewid 
			INNER JOIN vtiger_ossmailview_relation ON vtiger_ossmailview_relation.ossmailviewid = vtiger_ossmailview.ossmailviewid 
			LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid 
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid 
			WHERE vtiger_crmentity.deleted = 0 AND vtiger_ossmailview_relation.crmid = '$recordId'";
		$instance = CRMEntity::getInstance($moduleName);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($moduleName, false, $recordId);
		if ($securityParameter != '')
			$sql .= $securityParameter;
		return $sql;
	}

	/**
	 * Function to delete the current Record Model
	 */
	public function delete()
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery('UPDATE vtiger_ossmailview_relation SET `deleted` = ? WHERE ossmailviewid = ?;', [1, $this->getId()]);
		parent::delete();
	}
}
