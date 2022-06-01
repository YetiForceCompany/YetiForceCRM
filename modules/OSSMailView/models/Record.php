<?php

/**
 * OSSMailView record model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_Record_Model extends Vtiger_Record_Model
{
	const TYPE_COLORS = [
		0 => 'bgGreen',
		1 => 'bgDanger',
		2 => 'bgBlue',
	];
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
		if ('uid' === $key) {
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
		if ('All' === $filter || 'Contacts' === $filter) {
			$relatedId = (new \App\Db\Query())->select(['vtiger_contactdetails.contactid'])->from('vtiger_contactdetails')
				->innerJoin('vtiger_crmentity', 'vtiger_contactdetails.contactid = vtiger_crmentity.crmid')
				->where(['vtiger_contactdetails.parentid' => $srecord, 'deleted' => 0])->column();
		}
		if ('Contacts' !== $filter) {
			$relatedId[] = $srecord;
		}
		if (!$relatedId || false === App\ModuleHierarchy::getModuleLevel($smodule)) {
			return [];
		}
		$return = [];
		$subQuery = (new \App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview_relation')->where(['crmid' => $relatedId, 'deleted' => 0]);
		$query = (new \App\Db\Query())->select(['vtiger_ossmailview.*'])->from('vtiger_ossmailview')
			->innerJoin('vtiger_crmentity', 'vtiger_ossmailview.ossmailviewid = vtiger_crmentity.crmid')
			->where(['ossmailviewid' => $subQuery]);
		if ('All' !== $type) {
			$query->andWhere((['type' => $type]));
		}
		\App\PrivilegeQuery::getConditions($query, 'OSSMailView', false, $srecord);
		$query->orderBy(['date' => SORT_DESC]);
		if ('' !== $config['widget_limit']) {
			$query->limit($config['widget_limit']);
		}

		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$from = $this->findRecordsById($row['from_id']);
			$from = ($from && '' !== $from) ? $from : $row['from_email'];
			$to = $this->findRecordsById($row['to_id']);
			$to = ($to && '' !== $to) ? $to : $row['to_email'];
			$content = \App\Purifier::purifyHtml(vtlib\Functions::getHtmlOrPlainText($row['content']));
			if (\App\Privilege::isPermitted('OSSMailView', 'DetailView', $row['ossmailviewid'])) {
				$subject = '<a href="index.php?module=OSSMailView&view=Preview&record=' . $row['ossmailviewid'] . '" target="' . $config['target'] . '"> ' . \App\Purifier::encodeHtml($row['subject']) . '</a>';
			} else {
				$subject = \App\Purifier::encodeHtml($row['subject']);
			}
			$firstLetterBg = self::TYPE_COLORS[$row['type']] ?? '';
			$firstLetter = strtoupper(App\TextUtils::textTruncate(trim(strip_tags($from)), 1, false));
			if ($row['orginal_mail'] && '-' !== $row['orginal_mail']) {
				$rblInstance = \App\Mail\Rbl::getInstance([]);
				$rblInstance->set('rawBody', $row['orginal_mail']);
				$rblInstance->parse();
				if (($verifySender = $rblInstance->verifySender()) && !$verifySender['status']) {
					$firstLetter = '<span class="fas fa-exclamation-triangle text-danger" title="' . \App\Purifier::encodeHtml($verifySender['info']) . '"></span>';
					$firstLetterBg = 'bg-warning';
				}
			}
			$return[] = [
				'id' => $row['ossmailviewid'],
				'date' => $row['date'],
				'firstLetter' => $firstLetter,
				'firstLetterBg' => $firstLetterBg,
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
				'teaser' => App\TextUtils::textTruncate(\App\Utils::htmlToText($content), 190),
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
				if (!$recordMetaData || 1 === $recordMetaData['deleted']) {
					continue;
				}
				$module = $recordMetaData['setype'];
				if ('Leads' === $module) {
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

	/**
	 * Find email for record.
	 *
	 * @param int    $record
	 * @param string $module
	 *
	 * @return string
	 */
	public function findEmail(int $record, string $module): string
	{
		if (!\App\Record::isExists($record)) {
			return false;
		}
		$returnEmail = '';
		if (\in_array($module, ['HelpDesk', 'Project', 'SSalesProcesses'])) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $module);
			$returnEmail = $this->findEmailInRelated($recordModel);
			if (!$returnEmail) {
				$accountId = '';
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
					$returnEmail = $this->findEmail($accountId, \App\Record::getType($accountId));
				}
			}
		} else {
			$emailFields = OSSMailScanner_Record_Model::getEmailSearch($module);
			if (\count($emailFields) > 0) {
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

	/**
	 * Find email in related records.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return string
	 */
	public function findEmailInRelated(Vtiger_Record_Model $recordModel): string
	{
		$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, 'Contacts');
		$query = $relationListView->getRelationQuery();
		$tabIndex = $relationListView->getRelatedModuleModel()->getEntityInstance()->tab_name_index;
		$query->select(['vtiger_crmentity.crmid']);
		$emailFields = OSSMailScanner_Record_Model::getEmailSearch('Contacts');
		$where = ['or'];
		foreach ($emailFields as $fieldParams) {
			$query->addSelect([$fieldParams['fieldname'] => $fieldParams['tablename'] . '.' . $fieldParams['columnname']]);
			$where[] = ['<>', $fieldParams['tablename'] . '.' . $fieldParams['columnname'], ''];
			if (!\in_array($fieldParams['tablename'], $query->from) && !\in_array($fieldParams['tablename'], array_column($query->join, 1))) {
				$query->leftJoin($fieldParams['tablename'], "vtiger_crmentity.crmid = {$fieldParams['tablename']}.{$tabIndex[$fieldParams['tablename']]}");
			}
		}
		$query->andWhere($where);
		$dataReader = $query->createCommand()->query();
		$emails = [];
		while ($row = $dataReader->read()) {
			foreach ($emailFields as $fieldParams) {
				if (!empty($row[$fieldParams['fieldname']])) {
					$emails[] = $row[$fieldParams['fieldname']];
				}
			}
		}
		return implode(',', $emails);
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
		$this->addLog('Action_Bind', \count($selectedIds));
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
		$this->addLog('Action_ChangeType', \count($selectedIds));
		\App\Db::getInstance()->createCommand()->update('vtiger_ossmailview', ['ossmailview_sendtype' => $mailType[$mail_type], 'type' => $mail_type], ['ossmailviewid' => $selectedIds])->execute();
	}

	public function addLog($action, $info)
	{
		$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
		App\Db::getInstance()->createCommand()->insert('vtiger_ossmails_logs', ['action' => $action, 'info' => $info, 'user' => $user_id, 'start_time' => date('Y-m-d H:i:s')])->execute();
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
	 * @param object $mbox
	 * @param mixed  $folder
	 * @param mixed  $rcId
	 *
	 * @return bool|int
	 */
	public function checkMailExist($uid, $folder, $rcId, $mbox)
	{
		$mail = OSSMail_Record_Model::getMail($mbox, $uid, false);
		if (!$mail) {
			return false;
		}
		$where = ['cid' => $mail->getUniqueId()];
		if (!\Config\Modules\OSSMailScanner::$ONE_MAIL_FOR_MULTIPLE_RECIPIENTS) {
			$where['mbox'] = $folder;
			$where['rc_user'] = $rcId;
		}
		return (new App\Db\Query())->select(['ossmailviewid'])->from('vtiger_ossmailview')->where($where)->scalar();
	}

	/**
	 * Get related records.
	 *
	 * @param int $record
	 *
	 * @return array
	 */
	public function getRelatedRecords($record): array
	{
		$relations = [];
		$query = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])->from('vtiger_ossmailview_relation')->innerJoin('vtiger_crmentity', 'vtiger_ossmailview_relation.crmid = vtiger_crmentity.crmid')->where(['ossmailviewid' => $record, 'vtiger_crmentity.deleted' => 0]);
		$dataReader = $query->createCommand()->query();
		$moduleDocuments = Vtiger_Module_Model::getInstance('Documents');
		while ($row = $dataReader->read()) {
			$module = $row['setype'];
			$relations[$module][] = [
				'id' => $row['crmid'],
				'module' => $module,
				'label' => \App\Record::getLabel($row['crmid']),
				'is_related_to_documents' => false !== Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance($module), $moduleDocuments),
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
		if ('Products' === $newModule) {
			$dbCommand->insert('vtiger_seproductsrel', [
				'crmid' => (int) $params['crmid'],
				'productid' => $newCrmId,
				'setype' => $params['mod'],
				'rel_created_user' => $currentUser->getId(),
				'rel_created_time' => date('Y-m-d H:i:s'),
			])->execute();
		} elseif ('Services' === $newModule) {
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
	public function isEditable(): bool
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
