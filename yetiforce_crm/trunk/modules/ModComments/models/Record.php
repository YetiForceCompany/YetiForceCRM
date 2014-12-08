<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * ModComments Record Model
 */
class ModComments_Record_Model extends Vtiger_Record_Model {

	/**
	 * Functions gets the comment id
	 */
	public function getId() {
		//TODO : check why is modcommentsid is not set
		$id = $this->get('modcommentsid');
		if(empty($id)) {
			return $this->get('id');
		}
		return $this->get('modcommentsid');
	}

	public function setId($id) {
		return $this->set('modcommentsid', $id);
	}

	/**
	 * Function returns url to get child comments
	 * @return <String> - url
	 */
	public function getChildCommentsUrl() {
		return $this->getDetailViewUrl().'&mode=showChildComments';
	}

	public function getImagePath() {
		$commentor = $this->getCommentedByModel();
		if($commentor) {
			$customer = $this->get('customer');
            $isMailConverterType = $this->get('from_mailconverter');
			if (!empty($customer) && $isMailConverterType != 1) {
                $recordModel = Vtiger_Record_Model::getInstanceById($customer);
                $imageDetails = $recordModel->getImageDetails();
                if(!empty($imageDetails)) {
                    return $imageDetails[0]['path'].'_'.$imageDetails[0]['name'];
                } else
                    return vimage_path('CustomerPortal.png');
			} else if($isMailConverterType == 1) {
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
	 * Function to create an instance of ModComment_Record_Model
	 * @param <Integer> $record
	 * @return ModComment_Record_Model
	 */
	public static function getInstanceById($record) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT vtiger_modcomments.*, vtiger_crmentity.smownerid,
					vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime FROM vtiger_modcomments
					INNER JOIN vtiger_crmentity ON vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid
					WHERE modcommentsid = ? AND deleted = 0', array($record));
		if($db->num_rows($result)) {
			$row = $db->query_result_rowdata($result, $i);
			$self = new self();
			$self->setData($row);
			return $self;
		}
		return false;
	}

	/**
	 * Function returns the parent Comment Model
	 * @return <Vtiger_Record_Model>
	 */
	public function getParentCommentModel() {
		$recordId = $this->get('parent_comments');
		if(!empty($recordId))
			return ModComments_Record_Model::getInstanceById($recordId, 'ModComments');

		return false;
	}

	/**
	 * Function returns the parent Record Model(Contacts, Accounts etc)
	 * @return <Vtiger_Record_Model>
	 */
	public function getParentRecordModel() {
		$parentRecordId = $this->get('related_to');
		if(!empty($parentRecordId))
		return Vtiger_Record_Model::getInstanceById($parentRecordId);

		return false;
	}

	/**
	 * Function returns the commentor Model (Users Model)
	 * @return <Vtiger_Record_Model>
	 */
	public function getCommentedByModel() {
		$customer = $this->get('customer');
		if(!empty($customer)) {
			return Vtiger_Record_Model::getInstanceById($customer, 'Contacts');
		} else {
			$commentedBy = $this->get('smownerid');
			if($commentedBy) {
			    $commentedByModel = Vtiger_Record_Model::getInstanceById($commentedBy, 'Users');
			    if(empty($commentedByModel->entity->column_fields['user_name'])) {
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
	 * @return <String>
	 */
	public function getCommentedTime() {
		$commentTime = $this->get('createdtime');
		return $commentTime;
	}
	
	/**
	 * Function returns the commented time
	 * @return <String>
	 */
	public function getModifiedTime() {
		$commentTime = $this->get('modifiedtime');
		return $commentTime;
	}
	/**
	 * Function returns latest comments for parent record
	 * @param <Integer> $parentRecordId - parent record for which latest comment need to retrieved
	 * @param <Vtiger_Paging_Model> - paging model
	 * @return ModComments_Record_Model if exits or null
	 */
	public static function getRecentComments($parentRecordId, $pagingModel){
		$db = PearDatabase::getInstance();
		$startIndex = $pagingModel->getStartIndex();
		$limit = $pagingModel->getPageLimit();

		$listView = Vtiger_ListView_Model::getInstance('ModComments');
		$queryGenerator = $listView->get('query_generator');
		$queryGenerator->setFields(array('parent_comments', 'createdtime', 'modifiedtime', 'related_to',
									'assigned_user_id', 'commentcontent', 'creator', 'id', 'customer', 'reasontoedit', 'userid', 'from_mailconverter'));

		$query = $queryGenerator->getQuery();
		$query = $query ." AND related_to = ? ORDER BY vtiger_crmentity.createdtime DESC
							LIMIT $startIndex, $limit";

		$result = $db->pquery($query, array($parentRecordId));
		$rows = $db->num_rows($result);

		for ($i=0; $i<$rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
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
	public static function getAllParentComments($parentId) {
		$db = PearDatabase::getInstance();

		$listView = Vtiger_ListView_Model::getInstance('ModComments');
		$queryGenerator = $listView->get('query_generator');
		$queryGenerator->setFields(array('parent_comments', 'createdtime', 'modifiedtime', 'related_to', 'id',
											'assigned_user_id', 'commentcontent', 'creator', 'customer', 'reasontoedit', 'userid'));
		$query = $queryGenerator->getQuery();

		//Condition are directly added as query_generator transforms the
		//reference field and searches their entity names
		$query = $query ." AND related_to = ? AND parent_comments = '' ORDER BY vtiger_crmentity.createdtime DESC";
		
		$result = $db->pquery($query, array($parentId));
		$rows = $db->num_rows($result);

		for ($i=0; $i<$rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
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
	public function getChildCommentsCount() {
		$db = PearDatabase::getInstance();
		$parentRecordId = $this->get('related_to');

		$query = 'SELECT 1 FROM vtiger_modcomments WHERE parent_comments = ? AND related_to = ?';
		$result = $db->pquery($query, array($this->getId(), $parentRecordId));
		if($db->num_rows($result)) {
			return $db->num_rows($result);
		} else {
			return 0;
		}
	}

	/**
	 * Returns child comments models for a comment
	 * @return ModComment_Record_Model(s)
	 */
	public function getChildComments() {
		$db = PearDatabase::getInstance();
		$parentCommentId = $this->get('modcommentsid');

		if(empty($parentCommentId)) return;

		$parentRecordId = $this->get('related_to');

		$listView = Vtiger_ListView_Model::getInstance('ModComments');
		$queryGenerator = $listView->get('query_generator');
		$queryGenerator->setFields(array('parent_comments', 'createdtime', 'modifiedtime', 'related_to', 'id',
											'assigned_user_id', 'commentcontent', 'creator', 'reasontoedit', 'userid'));
		$query = $queryGenerator->getQuery();

		//Condition are directly added as query_generator transforms the
		//reference field and searches their entity names
		$query = $query. ' AND parent_comments = ? AND related_to = ?';

		$recordInstances = array();
		$result = $db->pquery($query, array($parentCommentId, $parentRecordId));
		$rows = $db->num_rows($result);
		for ($i=0; $i<$rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$recordInstance = new self();
			$recordInstance->setData($row);
			$recordInstances[] = $recordInstance;
		}
		
		return $recordInstances;
	}

	/**
	 * Function to get details for user have the permissions to do actions
	 * @return <Boolean> - true/false
	 */
	public function isEditable() {
		return false;
	}

	/**
	 * Function to get details for user have the permissions to do actions
	 * @return <Boolean> - true/false
	 */
	public function isDeletable() {
		return false;
	}
}
