<?php

/**
 * OSSMailView record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$this->modules_email_actions_widgets['SSingleOrders'] = true;
		parent::__construct();
	}

	public function get($key)
	{
		$value = parent::get($key);
		if ($key === 'content' && \App\Request::_get('view') === 'Detail') {
			return vtlib\Functions::getHtmlOrPlainText($value);
		}
		if ($key === 'uid' || $key === 'content') {
			return App\Purifier::decodeHtml($value);
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
	 * Function return emails list.
	 *
	 * @param int    $srecord
	 * @param string $smodule
	 * @param array  $config
	 * @param string $type
	 * @param string $filter
	 *
	 * @return string[]
	 */
	public function showEmailsList($srecord, $smodule, $config, $type, $filter = 'All')
	{
		if ($filter === 'All' || $filter === 'Contacts') {
			$relatedId = (new \App\Db\Query())->select(['vtiger_contactdetails.contactid'])->from('vtiger_contactdetails')
				->innerJoin('vtiger_crmentity', 'vtiger_contactdetails.contactid = vtiger_crmentity.crmid')
				->where(['vtiger_contactdetails.parentid' => $srecord, 'deleted' => 0])->column();
		}
		if ($filter !== 'Contacts') {
			$relatedId[] = $srecord;
		}
		if (!$relatedId || App\ModuleHierarchy::getModuleLevel($smodule) === false) {
			return [];
		}
		$return = [];
		$subQuery = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview_relation')->where(['crmid' => $relatedId, 'deleted' => 0]);
		$query = (new \App\Db\Query())->select(['vtiger_ossmailview.*'])->from('vtiger_ossmailview')
			->innerJoin('vtiger_crmentity', 'vtiger_ossmailview.ossmailviewid = vtiger_crmentity.crmid')
			->where(['ossmailviewid' => $subQuery]);
		if ($type !== 'All') {
			$query->andWhere((['type' => $type]));
		}
		\App\PrivilegeQuery::getConditions($query, 'OSSMailView', false, $srecord);
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
			$content = \App\Purifier::purifyHtml(vtlib\Functions::getHtmlOrPlainText($row['content']));
			if (\App\Privilege::isPermitted('OSSMailView', 'DetailView', $row['ossmailviewid'])) {
				$subject = '<a href="index.php?module=OSSMailView&view=Preview&record=' . $row['ossmailviewid'] . '" target="' . $config['target'] . '"> ' . \App\Purifier::encodeHtml($row['subject']) . '</a>';
			} else {
				$subject = \App\Purifier::encodeHtml($row['subject']);
			}
			$return[] = [
				'id' => $row['ossmailviewid'],
				'date' => $row['date'],
				'firstLetter' => strtoupper(App\TextParser::textTruncate(trim(strip_tags($from)), 1, false)),
				'subjectRaw' => \App\Purifier::encodeHtml($row['subject']),
				'subject' => $subject,
				'attachments' => $row['attachments_exist'],
				'from' => $from,
				'fromRaw' => $row['from_email'],
				'toRaw' => $row['to_email'],
				'ccRaw' => $row['cc_email'],
				'to' => $to,
				'url' => "index.php?module=OSSMailView&view=Preview&record={$row['ossmailviewid']}&srecord=$srecord&smodule=$smodule",
				'type' => $row['type'],
				'teaser' => App\TextParser::textTruncate(trim(preg_replace('/[ \t]+/', ' ', strip_tags($content))), 100),
				'body' => $content,
				'bodyRaw' => $row['content'],
			];
		}
		$dataReader->close();

		return $return;
	}

	/**
	 * Find records.
	 *
	 * @param int[] $ids
	 *
	 * @return string
	 */
	public function findRecordsById($ids)
	{
		$return = false;
		if (!empty($ids)) {
			$recordModelMailScanner = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
			$config = $recordModelMailScanner->getConfig('email_list');
			if (strpos($ids, ',')) {
				$idsArray = explode(',', $ids);
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
		if (!\App\Record::isExists($record)) {
			return false;
		}
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
				default:
					break;
			}
			if (\App\Record::isExists($accountId)) {
				$setype = \App\Record::getType($accountId);
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

	public function deleteRel($recordId)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$query = (new \App\Db\Query())->from('vtiger_ossmailview_files')->where(['ossmailviewid' => $recordId]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$dbCommand->update('vtiger_crmentity', ['deleted' => 1], ['crmid' => $row['documentsid']])->execute();
			$dbCommand->update('vtiger_crmentity', ['deleted' => 1], ['crmid' => $row['attachmentsid']])->execute();
		}
		$dataReader->close();
	}

	public function bindSelectedRecords($selectedIds)
	{
		$this->addLog('Action_Bind', count($selectedIds));
		\App\Db::getInstance()->createCommand()->update('vtiger_ossmailview', ['verify' => 1], ['ossmailviewid' => $selectedIds])->execute();
	}

	public static function getMailType()
	{
		return [2 => 'Internal', 0 => 'Sent', 1 => 'Received'];
	}

	public function changeTypeAllRecords($mailType)
	{
		$mailTypeData = self::getMailType();
		$this->addLog('Action_ChangeType', 'all');
		\App\Db::getInstance()->createCommand()->update('vtiger_ossmailview', ['ossmailview_sendtype' => $mailTypeData[$mailType], 'type' => $mailType], [])->execute();
	}

	public function changeTypeSelectedRecords($selectedIds, $mail_type)
	{
		$mailType = self::getMailType();
		$this->addLog('Action_ChangeType', count($selectedIds));
		\App\Db::getInstance()->createCommand()->update('vtiger_ossmailview', ['ossmailview_sendtype' => $mailType[$mail_type], 'type' => $mail_type], ['ossmailviewid' => $selectedIds])->execute();
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
		return $sql . \App\PrivilegeQuery::getAccessConditions($moduleName, false, $recordId);
	}

	/**
	 * Function to delete the current Record Model.
	 */
	public function delete()
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_ossmailview_relation', ['deleted' => 1], ['ossmailviewid' => $this->getId()])->execute();
		parent::delete();
	}

	/**
	 * Check if mail exist.
	 *
	 * @param int    $uid
	 * @param string $folder
	 * @param int    $rcId
	 *
	 * @return int|bool
	 */
	public function checkMailExist($uid, $folder, $rcId)
	{
		return (new App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where(['id' => $uid, 'mbox' => $folder, 'rc_user' => $rcId])->scalar();
	}

	/**
	 * Get related records.
	 *
	 * @param int $record
	 *
	 * @return array
	 */
	public function getRelatedRecords($record)
	{
		$relations = [];
		$query = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])->from('vtiger_ossmailview_relation')->innerJoin('vtiger_crmentity', 'vtiger_ossmailview_relation.crmid = vtiger_crmentity.crmid')->where(['ossmailviewid' => $record, 'vtiger_crmentity.deleted' => 0]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$module = $row['setype'];
			$relations[$module][] = [
				'id' => $row['crmid'],
				'module' => $module,
				'label' => \App\Record::getLabel($row['crmid']),
			];
		}
		$dataReader->close();

		return $relations;
	}

	/**
	 * Add related.
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public static function addRelated($params)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$newModule = $params['newModule'];
		$newCrmId = (int) $params['newCrmId'];
		if ($newModule === 'Products') {
			$dbCommand->insert('vtiger_seproductsrel', [
				'crmid' => (int) $params['crmid'],
				'productid' => $newCrmId,
				'setype' => $params['mod'],
				'rel_created_user' => $currentUser->getId(),
				'rel_created_time' => date('Y-m-d H:i:s'),
			])->execute();
		} elseif ($newModule === 'Services') {
			$dbCommand->insert('vtiger_crmentityrel', [
				'crmid' => (int) $params['crmid'],
				'module' => $params['mod'],
				'relcrmid' => $newCrmId,
				'relmodule' => $newModule,
			])->execute();
		} else {
			(new OSSMailView_Relation_Model())->addRelation((int) $params['mailId'], $newCrmId);
		}
		return \App\Language::translate('Add relationship', 'OSSMail');
	}

	/**
	 * Remove related.
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public static function removeRelated($params)
	{
		\App\Db::getInstance()->createCommand()->delete('vtiger_ossmailview_relation', ['ossmailviewid' => (int) $params['mailId'], 'crmid' => (int) $params['crmid']])->execute();

		return \App\Language::translate('Removed relationship', 'OSSMail');
	}

	/**
	 * Check if record is editable.
	 *
	 * @return bool
	 */
	public function isEditable()
	{
		return false;
	}

	/**
	 * Set reload relation record.
	 *
	 * @param string $moduleName
	 * @param int    $record
	 */
	public function setReloadRelationRecord($moduleName, $record = 0)
	{
		$exists = (new App\Db\Query())->from('s_#__mail_relation_updater')->where(['crmid' => $record])->exists();
		if (!$exists) {
			\App\Db::getInstance()->createCommand()->insert('s_#__mail_relation_updater', [
				'tabid' => \App\Module::getModuleId($moduleName),
				'crmid' => $record,
			])->execute();
		}
	}

	/**
	 * Returns basic information about atachments for this mail.
	 *
	 * @return array
	 */
	public function getAttachments()
	{
		return (new App\Db\Query())->select(['name' => 'vtiger_notes.title', 'file' => 'vtiger_notes.filename', 'id' => 'vtiger_notes.notesid'])
			->from('vtiger_notes')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_notes.notesid')
			->leftJoin('vtiger_ossmailview_files', 'vtiger_ossmailview_files.documentsid = vtiger_notes.notesid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_ossmailview_files.ossmailviewid' => $this->getId()])
			->all();
	}
}
