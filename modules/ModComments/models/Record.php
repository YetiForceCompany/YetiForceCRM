<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************** */

/**
 * ModComments Record Model
 */
class ModComments_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Functions gets the comment id
	 */
	public function getId()
	{

		$id = $this->get('modcommentsid');
		if (empty($id)) {
			return $this->get('id');
		}
		return $this->get('modcommentsid');
	}

	public function setId($id)
	{
		return $this->set('modcommentsid', $id);
	}

	/**
	 * Function returns url to get child comments
	 * @return string - url
	 */
	public function getChildCommentsUrl()
	{
		return $this->getDetailViewUrl() . '&mode=showChildComments';
	}

	public function getImagePath()
	{
		$commentor = $this->getCommentedByModel();
		if ($commentor) {
			$customer = $this->get('customer');
			$isMailConverterType = $this->get('from_mailconverter');
			if (!empty($customer) && $isMailConverterType != 1) {
				$recordModel = Vtiger_Record_Model::getInstanceById($customer);
				$imageDetails = $recordModel->getImageDetails();
				if (!empty($imageDetails)) {
					return $imageDetails[0]['path'] . '_' . $imageDetails[0]['name'];
				} else
					return '';
			} else if ($isMailConverterType == 1) {
				return vimage_path('MailConverterComment.png');
			} else {
				$imagePath = $commentor->getImageDetails();
				if (!empty($imagePath[0]['name'])) {
					return $imagePath[0]['path'] . '_' . $imagePath[0]['name'];
				}
			}
		}
		return false;
	}

	/**
	 * Prepare value to save
	 * @return array
	 */
	public function getValuesForSave()
	{
		$forSave = parent::getValuesForSave();
		$forSave['vtiger_modcomments']['userid'] = App\User::getCurrentUserId();
		return $forSave;
	}

	/**
	 * Function returns the parent Comment Model
	 * @return <Vtiger_Record_Model>
	 */
	public function getParentCommentModel()
	{
		$recordId = $this->get('parent_comments');
		if (!empty($recordId))
			return Vtiger_Record_Model::getInstanceById($recordId, 'ModComments');

		return false;
	}

	/**
	 * Function returns the parent Record Model(Contacts, Accounts etc)
	 * @return <Vtiger_Record_Model>
	 */
	public function getParentRecordModel()
	{
		$parentRecordId = $this->get('related_to');
		if (!empty($parentRecordId))
			return Vtiger_Record_Model::getInstanceById($parentRecordId);

		return false;
	}

	/**
	 * Function returns the commentor Model (Users Model)
	 * @return Vtiger_Record_Model|false
	 */
	public function getCommentedByModel()
	{
		$customer = $this->get('customer');
		if (!empty($customer)) {
			return Vtiger_Record_Model::getInstanceById($customer, 'Contacts');
		} else {
			$commentedBy = $this->get('assigned_user_id');
			if ($commentedBy) {
				$commentedByModel = Vtiger_Record_Model::getInstanceById($commentedBy, 'Users');
				if (empty($commentedByModel->entity->column_fields['user_name'])) {
					$activeAdmin = Users::getActiveAdminUser();
					$commentedByModel = Vtiger_Record_Model::getInstanceById($activeAdmin->id, 'Users');
				}
				return $commentedByModel;
			}
		}
		return false;
	}

	/**
	 * Function returns the commented time
	 * @return string
	 */
	public function getCommentedTime()
	{
		$commentTime = $this->get('createdtime');
		return $commentTime;
	}

	/**
	 * Function returns the commented time
	 * @return string
	 */
	public function getModifiedTime()
	{
		$commentTime = $this->get('modifiedtime');
		return $commentTime;
	}

	/**
	 * Function returns latest comments for parent record
	 * @param int $parentRecordId - parent record for which latest comment need to retrieved
	 * @param Vtiger_Paging_Model - paging model
	 * @return ModComments_Record_Model if exits or null
	 */
	public static function getRecentComments($parentRecordId, $pagingModel)
	{
		$recordInstances = [];
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$queryGenerator->setFields(['id', 'parent_comments', 'createdtime', 'modifiedtime', 'related_to', 'assigned_user_id', 'commentcontent', 'creator', 'customer', 'reasontoedit', 'userid', 'from_mailconverter']);
		$queryGenerator->setSourceRecord($parentRecordId);
		$queryGenerator->addNativeCondition(['related_to' => $parentRecordId]);
		$query = $queryGenerator->createQuery()->orderBy(['vtiger_crmentity.createdtime' => SORT_DESC]);
		if ($pagingModel->get('limit') !== 'no_limit') {
			$query->limit($pagingModel->getPageLimit())->offset($pagingModel->getStartIndex());
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$recordInstance = new self();
			$recordInstance->setData($row);
			$recordInstances[] = $recordInstance;
		}
		return $recordInstances;
	}

	/**
	 * Function returns all the parent comments model
	 * @param <Integer> $parentId
	 * @return ModComments_Record_Model(s)
	 */
	public static function getAllParentComments($parentId, $hierarchy = false)
	{
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$queryGenerator->setFields(['parent_comments', 'createdtime', 'modifiedtime', 'related_to', 'id',
			'assigned_user_id', 'commentcontent', 'creator', 'customer', 'reasontoedit', 'userid']);
		$queryGenerator->setSourceRecord($parentId);
		if (empty($hierarchy) || (count($hierarchy) == 1 && reset($hierarchy) == 0)) {
			$queryGenerator->addNativeCondition(['related_to' => $parentId]);
		} else {
			$recordIds = \App\ModuleHierarchy::getRelatedRecords($parentId, $hierarchy);
			if (empty($recordIds)) {
				return [];
			}
			$queryGenerator->addNativeCondition(['related_to' => $recordIds]);
		}
		$queryGenerator->addNativeCondition(['parent_comments' => 0]);
		$dataReader = $queryGenerator->createQuery()
				->orderBy(['vtiger_crmentity.createdtime' => SORT_DESC])
				->createCommand()->query();
		$recordInstances = [];
		while ($row = $dataReader->read()) {
			$recordInstance = new self();
			$recordInstance->setData($row);
			$recordInstances[] = $recordInstance;
		}
		return $recordInstances;
	}

	/**
	 * Function returns all the child comment count
	 * @return <type>
	 */
	public function getChildCommentsCount()
	{
		$recordId = $this->getId();
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$queryGenerator->setFields([]);
		$queryGenerator->setSourceRecord($recordId);
		$queryGenerator->addNativeCondition(['parent_comments' => $recordId, 'related_to' => $this->get('related_to')]);
		return $queryGenerator->createQuery()->count();
	}

	/**
	 * Function returns all the comment count
	 * @return <int>
	 */
	public static function getCommentsCount($recordId)
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
	 * Returns child comments models for a comment
	 * @return ModComment_Record_Model(s)
	 */
	public function getChildComments()
	{
		$parentCommentId = $this->getId();
		if (empty($parentCommentId))
			return;
		$queryGenerator = new \App\QueryGenerator('ModComments');
		$queryGenerator->setFields(array('parent_comments', 'createdtime', 'modifiedtime', 'related_to', 'id',
			'assigned_user_id', 'commentcontent', 'creator', 'reasontoedit', 'userid'));
		//Condition are directly added as query_generator transforms the
		//reference field and searches their entity names
		$queryGenerator->addNativeCondition(['parent_comments' => $parentCommentId, 'related_to' => $this->get('related_to')]);
		$datareader = $queryGenerator->createQuery()->createCommand()->query();
		$recordInstances = [];
		while ($row = $datareader->read()) {
			$recordInstance = new self();
			$recordInstance->setData($row);
			$recordInstances[] = $recordInstance;
		}
		return $recordInstances;
	}

	/**
	 * Function to get details for user have the permissions to do actions
	 * @return boolean - true/false
	 */
	public function isDeletable()
	{
		return false;
	}
}
