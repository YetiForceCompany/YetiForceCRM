<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

/**
 * ModComments Record Model.
 */
class ModComments_Record_Model extends Vtiger_Record_Model
{
	/** @var \Vtiger_Record_Model Commentator record model instance. */
	private $commentatorModel;

	/** @var \Vtiger_Record_Model Parent record model instance. */
	private $parentModel;

	/** @var \Vtiger_Record_Model Parent comment record model instance. */
	private $parentCommentModel;

	/** {@inheritdoc} */
	public function getId()
	{
		$id = $this->get('modcommentsid');
		if (empty($id)) {
			return $this->get('id');
		}
		return $this->get('modcommentsid');
	}

	/** {@inheritdoc} */
	public function setId($id)
	{
		return $this->set('modcommentsid', $id);
	}

	/** {@inheritdoc} */
	public function getDisplayValue($fieldName, $record = false, $rawText = false, $length = false)
	{
		if ('commentcontent' !== $fieldName) {
			return parent::getDisplayValue($fieldName, $record, $rawText, $length);
		}
		if (empty($record)) {
			$record = $this->getId();
		}
		$value = \App\Purifier::purifyHtml($this->get($fieldName));
		if (!$rawText) {
			$value = \App\Utils\Completions::decode($value);
		}
		return $length ? \App\Layout::truncateHtml($value) : $value;
	}

	/** {@inheritdoc} */
	public function isEditable(): bool
	{
		if (!isset($this->privileges['isEditable'])) {
			return $this->privileges['isEditable'] = \App\User::getCurrentUserRealId() === (int) $this->get('userid') && parent::isEditable() && $this->isPermitted('EditableComments');
		}
		return $this->privileges['isEditable'];
	}

	/**
	 * Function returns url to get child comments.
	 *
	 * @return string - url
	 */
	public function getChildCommentsUrl()
	{
		return $this->getDetailViewUrl() . '&mode=showChildComments';
	}

	/**
	 * Prepare value to save.
	 *
	 * @return array
	 */
	public function getValuesForSave()
	{
		$forSave = parent::getValuesForSave();
		if (empty($forSave['vtiger_modcomments']['userid'])) {
			$forSave['vtiger_modcomments']['userid'] = App\User::getCurrentUserRealId();
		}
		return $forSave;
	}

	/**
	 * Function returns the parent Comment Model.
	 *
	 * @return Vtiger_Record_Model|null
	 */
	public function getParentCommentModel(): ?Vtiger_Record_Model
	{
		if (isset($this->parentCommentModel)) {
			return $this->parentCommentModel;
		}
		$parentCommentModel = null;
		if ($recordId = $this->get('parent_comments')) {
			$parentCommentModel = Vtiger_Record_Model::getInstanceById($recordId, 'ModComments');
		}
		return $this->parentCommentModel = $parentCommentModel;
	}

	/**
	 * Function returns the parent Record Model(Contacts, Accounts etc).
	 *
	 * @return Vtiger_Record_Model|null
	 */
	public function getParentRecordModel(): ?Vtiger_Record_Model
	{
		if (isset($this->parentModel)) {
			return $this->parentModel;
		}
		$parentModel = null;
		if ($parentRecordId = $this->get('related_to')) {
			$parentModel = Vtiger_Record_Model::getInstanceById($parentRecordId);
		}
		return $this->parentModel = $parentModel;
	}

	/**
	 * Function returns the commentator Model (Users Model).
	 *
	 * @return Vtiger_Record_Model|null
	 */
	public function getCommentedByModel(): ?Vtiger_Record_Model
	{
		if (isset($this->commentatorModel)) {
			return $this->commentatorModel;
		}
		if ($customer = $this->get('customer')) {
			$this->commentatorModel = Vtiger_Record_Model::getInstanceById($customer, 'Contacts');
		} elseif (($commentedBy = $this->get('assigned_user_id')) && \App\User::isExists($commentedBy, false)) {
			$this->commentatorModel = Vtiger_Record_Model::getInstanceById($commentedBy, 'Users');
		}

		return $this->commentatorModel;
	}

	/**
	 * Get commentator image.
	 *
	 * @return array|string
	 */
	public function getImage()
	{
		if (1 == $this->get('from_mailconverter')) {
			return \App\Layout::getImagePath('MailConverterComment.png');
		}
		if (($commentator = $this->getCommentedByModel()) && ($imagePath = $commentator->getImage())) {
			return $imagePath;
		}
		return [];
	}

	/**
	 * Get commentator name.
	 *
	 * @return string
	 */
	public function getCommentatorName(): string
	{
		$label = '';
		if ($this->get('assigned_user_id')) {
			$label = $this->getDisplayValue('assigned_user_id');
		}
		if ($this->get('customer') && ($contact = $this->getDisplayValue('customer'))) {
			$label = $label ? "$contact ($label)" : $label;
		}
		return $label;
	}

	/**
	 * Function returns the commented time.
	 *
	 * @return string
	 */
	public function getCommentedTime()
	{
		return $this->get('createdtime');
	}

	/**
	 * Function returns the commented time.
	 *
	 * @return string
	 */
	public function getModifiedTime()
	{
		return $this->get('modifiedtime');
	}

	/**
	 * Function returns all the parent comments model.
	 *
	 * @param int                 $parentId
	 * @param int[]               $hierarchy
	 * @param Vtiger_Paging_Model $pagingModel
	 * @param string              $moduleName
	 *
	 * @return \ModComments_Record_Model[]
	 */
	public static function getAllParentComments(int $parentId, string $moduleName, array $hierarchy = [], Vtiger_Paging_Model $pagingModel = null)
	{
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$queryGenerator->setFields(array_merge(array_keys(\App\Field::getModuleFieldInfosByPresence('ModComments')), ['id']));
		$queryGenerator->setSourceRecord($parentId);
		$moduleLevel = \App\ModuleHierarchy::getModuleLevel($moduleName);
		$requireCount = false === $moduleLevel || \in_array($moduleLevel, $hierarchy) ? 1 : 0;
		if (\count($hierarchy) > $requireCount && ($query = \App\ModuleHierarchy::getQueryRelatedRecords($parentId, $hierarchy))) {
			if ($requireCount) {
				$query->union((new \App\Db\Query())->select([new \yii\db\Expression($parentId)]));
			}
			$where = ['related_to' => (new \App\Db\Query())->select(['id'])->from(['temp_query' => $query])];
		} elseif ($requireCount) {
			$where = ['related_to' => $parentId];
		} else {
			$where = ['related_to' => 0];
		}
		$queryGenerator->addNativeCondition($where);
		$queryGenerator->addNativeCondition(['parent_comments' => 0]);
		$query = $queryGenerator->createQuery()->orderBy(['vtiger_crmentity.createdtime' => SORT_DESC]);
		if ($pagingModel && 0 !== $pagingModel->get('limit')) {
			$query->limit($pagingModel->getPageLimit())->offset($pagingModel->getStartIndex());
		}
		$dataReader = $query->createCommand()->query();
		$recordInstances = [];
		while ($row = $dataReader->read()) {
			$recordInstance = new self();
			$recordInstance->setData($row)->setModuleFromInstance($queryGenerator->getModuleModel());
			$recordInstances[] = $recordInstance;
		}
		$dataReader->close();

		return $recordInstances;
	}

	/**
	 * Function returns all the child comment count.
	 *
	 * @return int
	 */
	public function getChildCommentsCount(): int
	{
		$recordId = $this->getId();
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$queryGenerator->setFields([]);
		$queryGenerator->setSourceRecord($recordId);
		$queryGenerator->addNativeCondition(['parent_comments' => $recordId, 'related_to' => $this->get('related_to')]);
		return $queryGenerator->createQuery()->count();
	}

	/**
	 * Function returns all the comment count.
	 *
	 * @param int $recordId
	 *
	 * @return int
	 */
	public static function getCommentsCount(int $recordId): int
	{
		if (empty($recordId)) {
			return 0;
		}
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$queryGenerator->setFields([]);
		$queryGenerator->setSourceRecord($recordId);
		$queryGenerator->addNativeCondition(['related_to' => $recordId]);

		return $queryGenerator->createQuery()->count('modcommentsid');
	}

	/**
	 * Returns child comments models for a comment.
	 *
	 * @return ModComment_Record_Model[]
	 */
	public function getChildComments()
	{
		$parentCommentId = $this->getId();
		if (empty($parentCommentId)) {
			return;
		}
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$queryGenerator->setFields(array_merge(array_keys(\App\Field::getModuleFieldInfosByPresence('ModComments')), ['id']));
		//Condition are directly added as query_generator transforms the
		//reference field and searches their entity names
		$queryGenerator->addNativeCondition(['parent_comments' => $parentCommentId, 'related_to' => $this->get('related_to')]);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$recordInstances = [];
		while ($row = $dataReader->read()) {
			$recordInstance = new self();
			$recordInstance->setData($row)->setModuleFromInstance($queryGenerator->getModuleModel());
			$recordInstances[] = $recordInstance;
		}
		return $recordInstances;
	}

	/**
	 * Returns parent comment models for a comment.
	 *
	 * @return ModComment_Record_Model[]
	 */
	public function getParentComments()
	{
		$commentId = $this->getId();
		$parentCommentId = explode('::', $this->get('parents'))[0];
		if (empty($commentId) || empty($parentCommentId)) {
			return;
		}
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$queryGenerator->setFields(array_merge(array_keys(\App\Field::getModuleFieldInfosByPresence('ModComments')), ['id']));
		$queryGenerator->addNativeCondition(['modcommentsid' => $parentCommentId, 'related_to' => $this->get('related_to')]);
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$recordInstances = [];
		while ($row = $dataReader->read()) {
			$recordInstance = new self();
			$recordInstance->setData($row)->setModuleFromInstance($queryGenerator->getModuleModel());
			$recordInstances[] = $recordInstance;
		}
		return $recordInstances;
	}

	/**
	 * Function returns all the parent comments model.
	 *
	 * @param int                 $parentId
	 * @param string              $searchValue
	 * @param bool                $isWidget
	 * @param int[]               $hierarchy
	 * @param Vtiger_Paging_Model $pagingModel
	 * @param mixed               $moduleName
	 *
	 * @return \ModComments_Record_Model[]
	 */
	public static function getSearchComments(int $parentId, $moduleName, string $searchValue, bool $isWidget, array $hierarchy = [], Vtiger_Paging_Model $pagingModel = null)
	{
		$fields = array_merge(array_keys(\App\Field::getModuleFieldInfosByPresence('ModComments')), ['id']);
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$queryGenerator->setFields($fields);
		$queryGenerator->setSourceRecord($parentId);
		$where = ['or'];
		$requireCount = 0;
		$moduleLevel = \App\ModuleHierarchy::getModuleLevel($moduleName);

		if (false === $moduleLevel || \in_array($moduleLevel, $hierarchy)) {
			$where[] = ['related_to' => $parentId];
			$requireCount = 1;
		}
		if (\count($hierarchy) > $requireCount && ($query = \App\ModuleHierarchy::getQueryRelatedRecords($parentId, $hierarchy))) {
			$where[] = ['related_to' => (new \App\Db\Query())->select(['id'])->from(['temp_query' => $query])];
		}
		$queryGenerator->addNativeCondition($where);
		$queryGenerator->addNativeCondition(['like', 'commentcontent', '%' . $searchValue . '%', false]);
		$query = $queryGenerator->createQuery()->orderBy(['vtiger_crmentity.createdtime' => SORT_DESC]);
		if ($pagingModel && 0 !== $pagingModel->get('limit')) {
			$query->limit($pagingModel->getPageLimit())->offset($pagingModel->getStartIndex());
		}
		$dataReader = $query->createCommand()->query();
		$recordInstances = [];
		if ($isWidget) {
			while ($row = $dataReader->read()) {
				$recordInstance = new self();
				$recordInstance->setData($row)->setModuleFromInstance($queryGenerator->getModuleModel());
				$recordInstances[] = $recordInstance;
			}
			$dataReader->close();
		} else {
			$commentsId = [];
			while ($row = $dataReader->read()) {
				$parentTempId = strstr($row['parents'], '::', true) ?: $row['parents'];
				if (!empty($parentTempId) && !$isWidget) {
					$commentsId[] = $parentTempId;
				} else {
					$commentsId[] = $row['id'];
				}
			}
			if (!empty($commentsId)) {
				$queryGeneratorParents = new \App\QueryGenerator('ModComments');
				$queryGeneratorParents->setFields($fields);
				$queryGeneratorParents->addNativeCondition(['in', 'modcommentsid', array_unique($commentsId)], false);
				$parentQuery = $queryGeneratorParents->createQuery();
				if ($pagingModel && 0 !== $pagingModel->get('limit')) {
					$parentQuery->limit($pagingModel->getPageLimit())->offset($pagingModel->getStartIndex());
				}
				$dataReaderParents = $parentQuery->createCommand()->query();
				while ($row = $dataReaderParents->read()) {
					$recordInstance = new self();
					$recordInstance->setData($row)->setModuleFromInstance($queryGeneratorParents->getModuleModel());
					$recordInstances[] = $recordInstance;
				}
			}
		}
		return $recordInstances;
	}

	/**
	 * Function to get the list view actions for the comment.
	 *
	 * @return Vtiger_Link_Model[] - Associate array of Vtiger_Link_Model instances
	 */
	public function getCommentLinks()
	{
		$links = [];
		$stateColors = App\Config::search('LIST_ENTITY_STATE_COLOR');
		if ($this->privilegeToArchive()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_ARCHIVE_RECORD',
				'title' => \App\Language::translate('LBL_ARCHIVE_RECORD'),
				'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Archived&sourceView=List&record=' . $this->getId(),
				'linkdata' => ['confirm' => \App\Language::translate('LBL_ARCHIVE_RECORD_DESC'), 'source-view' => 'DetailTab'],
				'linkicon' => 'fas fa-archive',
				'linkclass' => 'btn-md m-0 px-1 py-0 js-action-confirm',
				'style' => empty($stateColors['Archived']) ? '' : "color: {$stateColors['Archived']};",
				'showLabel' => false,
			]);
		}
		if ($this->privilegeToMoveToTrash()) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues([
				'linklabel' => 'LBL_MOVE_TO_TRASH',
				'title' => \App\Language::translate('LBL_MOVE_TO_TRASH'),
				'dataUrl' => 'index.php?module=' . $this->getModuleName() . '&action=State&state=Trash&sourceView=List&record=' . $this->getId(),
				'linkdata' => ['confirm' => \App\Language::translate('LBL_MOVE_TO_TRASH_DESC'), 'source-view' => 'DetailTab'],
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn-md text-danger m-0 px-1 py-0 js-action-confirm',
				'showLabel' => false,
			]);
		}
		if ($link = \App\Fields\ServerAccess::getLinks($this, 'ModComments')) {
			$links[] = $link;
		}
		return $links;
	}
}
