<?php

/**
 * OSSMailView record model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailView_Record_Model extends Vtiger_Record_Model
{

	protected $modules_email_actions_widgets = [];

	public function __construct()
	{
		$this->modules_email_actions_widgets['Accounts'] = true;
		$this->modules_email_actions_widgets['Contacts'] = true;
		$this->modules_email_actions_widgets['Leads'] = true;
		$this->modules_email_actions_widgets['HelpDesk'] = true;
		$this->modules_email_actions_widgets['Project'] = true;
		$this->modules_email_actions_widgets['SSalesProcesses'] = true;
		parent::__construct();
	}

	public function get($key)
	{
		$value = parent::get($key);
		if ($key === 'content' && \App\Request::_get('view') === 'Detail') {
			return vtlib\Functions::getHtmlOrPlainText($value);
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

	/**
	 * Function return emails list
	 * @param int $srecord
	 * @param string $smodule
	 * @param array $config
	 * @param string $type
	 * @param string $filter
	 * @return string[]
	 */
	public function showEmailsList($srecord, $smodule, $config, $type, $filter = 'All')
	{
		$return = [];
		$widgets = $this->modules_email_actions_widgets;
		if (!empty($widgets[$smodule])) {
			if ($filter === 'All' || $filter === 'Contacts') {
				$relatedId = (new \App\Db\Query())->select(['vtiger_contactdetails.contactid'])->from('vtiger_contactdetails')
						->innerJoin('vtiger_crmentity', 'vtiger_contactdetails.contactid = vtiger_crmentity.crmid')
						->where(['vtiger_contactdetails.parentid' => $srecord, 'deleted' => 0])->column();
			}
			if ($filter !== 'Contacts') {
				$relatedId[] = $srecord;
			}
			if (!$relatedId) {
				return [];
			}
			$subQuery = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview_relation')->where(['crmid' => $relatedId, 'deleted' => 0])->orderBy(['date' => SORT_DESC])->column();
			$query = (new \App\Db\Query())->select(['vtiger_ossmailview.*'])->from('vtiger_ossmailview')
				->innerJoin('vtiger_crmentity', 'vtiger_ossmailview.ossmailviewid = vtiger_crmentity.crmid')
				->where(['ossmailviewid' => $subQuery]);
			if ($type !== 'All') {
				$query->andWhere((['type' => $type]));
			}
			\App\PrivilegeQuery::getConditions($query, 'OSSMailView');
			$query->orderBy(['date' => SORT_DESC]);
			if ($config['widget_limit'] !== '') {
				$query->limit($config['widget_limit']);
			}
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$from = $this->findRecordsById($row['from_id']);
				$from = ($from && $from !== '') ? $from : $row['from_email'];
				$to = $this->findRecordsById($row['to_id']);
				$to = ($to && $to !== '') ? $to : $row['to_email'];
				$content = vtlib\Functions::getHtmlOrPlainText($row['content']);
				if (\App\Privilege::isPermitted('OSSMailView', 'DetailView', $row['ossmailviewid'])) {
					$subject = '<a href="index.php?module=OSSMailView&view=preview&record=' . $row['ossmailviewid'] . '" target="' . $config['target'] . '"> ' . $row['subject'] . '</a>';
				} else {
					$subject = $row['subject'];
				}
				$return[] = [
					'id' => $row['ossmailviewid'],
					'date' => $row['date'],
					'firstLetter' => strtoupper(vtlib\Functions::textLength(trim(strip_tags($from)), 1, false)),
					'subjectRaw' => $row['subject'],
					'subject' => $subject,
					'attachments' => $row['attachments_exist'],
					'from' => $from,
					'fromRaw' => $row['from_email'],
					'toRaw' => $row['to_email'],
					'ccRaw' => $row['cc_email'],
					'to' => $to,
					'url' => "index.php?module=OSSMailView&view=preview&record={$row['ossmailviewid']}&srecord=$srecord&smodule=$smodule",
					'type' => $row['type'],
					'teaser' => vtlib\Functions::textLength(trim(preg_replace('/[ \t]+/', ' ', strip_tags($content))), 100),
					'body' => $content,
					'bodyRaw' => $row['content'],
				];
			}
		}
		return $return;
	}

	/**
	 * Find records
	 * @param int[] $ids
	 * @return string
	 */
	public function findRecordsById($ids)
	{
		$return = false;
		if (!empty($ids)) {
			$recordModelMailScanner = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
			$config = $recordModelMailScanner->getConfig('email_list');
			if (strpos($ids, ',')) {
				$idsArray = explode(",", $ids);
			} else {
				$idsArray[0] = $ids;
			}
			foreach ($idsArray as $id) {
				$recordMetaData = \vtlib\Functions::getCRMRecordMetadata($id);
				if (!$recordMetaData || $recordMetaData['deleted'] === 1) {
					continue;
				}
				$module = $recordMetaData['setype'];
				if ($module === 'Leads') {
					$isExists = (new \App\Db\Query())->from('vtiger_leaddetails')->where(['leadid' => $id, 'converted' => 0])->exists();
					if (!$isExists) {
						continue;
					}
				}
				if (\App\Privilege::isPermitted($module, 'DetailView', $id)) {
					$label = \App\Record::getLabel($id);
					$return .= '<a href="index.php?module=' . $module . '&view=Detail&record=' . $id . '" target="' . $config['target'] . '"> ' . $label . '</a>,';
				}
			}
		}
		return trim($return, ',');
	}

	public function findEmail($record, $module)
	{
		if (!isRecordExists($record))
			return false;
		$returnEmail = '';
		if (in_array($module, ['HelpDesk', 'Project', 'SSalesProcesses'])) {
			$accountId = '';
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
			switch ($module) {
				case 'HelpDesk':
					$accountId = $recordModel->get('parent_id');
					break;
				case 'Project':
					$accountId = $recordModel->get('linktoaccountscontacts');
					break;
				case 'SSalesProcesses':
					$accountId = $recordModel->get('related_to');
					break;
			}
			if (isRecordExists($accountId)) {
				$setype = vtlib\Functions::getCRMRecordType($accountId);
				$returnEmail = $this->findEmail($accountId, $setype);
			}
		} else {
			$emailFields = OSSMailScanner_Record_Model::getEmailSearch($module);
			if (count($emailFields) > 0) {
				$recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
				foreach ($emailFields as $emailField) {
					$email = $recordModel->get($emailField['columnname']);
					if (!empty($email)) {
						$returnEmail = $email;
						break;
					}
				}
			}
		}
		return $returnEmail;
	}

	public function delete_rel($recordId)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT * FROM vtiger_ossmailview_files WHERE ossmailviewid = ? ", array($recordId), true);
		$countResult = $adb->num_rows($result);
		for ($i = 0; $i < $countResult; $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			$adb->pquery("UPDATE vtiger_crmentity SET deleted = '1' WHERE crmid = ?", array($row['documentsid']), true);
			$adb->pquery("UPDATE vtiger_crmentity SET deleted = '1' WHERE crmid = ?; ", array($row['attachmentsid']), true);
		}
	}

	public function bindSelectedRecords($selectedIds)
	{
		$db = PearDatabase::getInstance();
		$this->addLog('Action_Bind', count($selectedIds));
		$db->pquery(sprintf('UPDATE vtiger_ossmailview SET `verify` = ? WHERE ossmailviewid in (%s);', $db->generateQuestionMarks($selectedIds)), [1, $selectedIds]);
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
		$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
		App\Db::getInstance()->createCommand()->insert('vtiger_ossmails_logs', ['action' => $action, 'info' => $info, 'user' => $user_id, 'start_time' => date('Y-m-d H:i:s')])->execute();
	}

	public function getMailsQuery($recordId, $moduleName)
	{
		$usersSqlFullName = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
		$sql = "SELECT vtiger_crmentity.*, vtiger_ossmailview.*, CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $usersSqlFullName ELSE vtiger_groups.groupname END AS user_name 
			FROM vtiger_ossmailview 
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossmailview.ossmailviewid 
			INNER JOIN vtiger_ossmailviewcf ON vtiger_ossmailviewcf.ossmailviewid = vtiger_ossmailview.ossmailviewid 
			INNER JOIN vtiger_ossmailview_relation ON vtiger_ossmailview_relation.ossmailviewid = vtiger_ossmailview.ossmailviewid 
			LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid 
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid 
			WHERE vtiger_crmentity.deleted = 0 && vtiger_ossmailview_relation.crmid = '$recordId'";
		$sql .= \App\PrivilegeQuery::getAccessConditions($moduleName, false, $recordId);
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

	public function checkMailExist($uid, $folder, $rcId)
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT ossmailviewid FROM vtiger_ossmailview WHERE id = ? && mbox = ? && rc_user = ?';
		$result = $db->pquery($query, [$uid, $folder, $rcId]);
		return $db->getRowCount($result) > 0 ? $db->getSingleValue($result) : false;
	}

	public function getRelatedRecords($record)
	{
		$db = PearDatabase::getInstance();
		$relations = [];
		$query = 'SELECT vtiger_crmentity.crmid, vtiger_crmentity.setype FROM vtiger_ossmailview_relation'
			. ' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossmailview_relation.crmid'
			. ' WHERE ossmailviewid = ? && vtiger_crmentity.deleted = ? ';
		$result = $db->pquery($query, [$record, 0]);
		while ($row = $db->getRow($result)) {
			$module = $row['setype'];
			$relations[$module][] = [
				'id' => $row['crmid'],
				'module' => $module,
				'label' => \App\Record::getLabel($row['crmid'])
			];
		}
		return $relations;
	}

	public static function addRelated($params)
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$crmid = $params['crmid'];
		$newModule = $params['newModule'];
		$newCrmId = $params['newCrmId'];
		$mailId = $params['mailId'];

		if ($newModule == 'Products') {
			$db->insert('vtiger_seproductsrel', [
				'crmid' => $crmid,
				'productid' => $newCrmId,
				'setype' => $params['mod'],
				'rel_created_user' => $currentUser->getId(),
				'rel_created_time' => date('Y-m-d H:i:s')
			]);
		} elseif ($newModule == 'Services') {
			$db->insert('vtiger_crmentityrel', [
				'crmid' => $crmid,
				'module' => $params['mod'],
				'relcrmid' => $newCrmId,
				'relmodule' => $newModule
			]);
		} else {
			(new OSSMailView_Relation_Model())->addRelation($mailId, $newCrmId);
		}
		return \App\Language::translate('Add relationship', 'OSSMail');
	}

	public static function removeRelated($params)
	{
		$db = PearDatabase::getInstance();
		$db->delete('vtiger_ossmailview_relation', 'ossmailviewid = ? && crmid = ?', [$params['mailId'], $params['crmid']]);
		return \App\Language::translate('Removed relationship', 'OSSMail');
	}

	public function isEditable()
	{
		return false;
	}

	public function setReloadRelationRecord($moduleName, $record = 0)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM s_yf_mail_relation_updater WHERE crmid = ?', [$record]);
		if ($db->getRowCount($result) == 0) {
			\App\Db::getInstance()->createCommand()->insert('s_#__mail_relation_updater', [
				'tabid' => \App\Module::getModuleId($moduleName),
				'crmid' => $record
			]);
		}
	}
}
