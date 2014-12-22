<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Emails_MassSaveAjax_View extends Vtiger_Footer_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('massSave');
	}

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		if (!Users_Privileges_Model::isPermitted($moduleName, 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function Sends/Saves mass emails
	 * @param <Vtiger_Request> $request
	 */
	public function massSave(Vtiger_Request $request) {
        global $upload_badext;
        $adb = PearDatabase::getInstance();

		$moduleName = $request->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$recordIds = $this->getRecordsListFromRequest($request);
		$documentIds = $request->get('documentids');

		// This is either SENT or SAVED
		$flag = $request->get('flag');

		$result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
		$_FILES = $result['file'];

        $recordId = $request->get('record');

        if(!empty($recordId)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId,$moduleName);
            $recordModel->set('mode', 'edit');
        }else{
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
            $recordModel->set('mode', '');
        }


        $parentEmailId = $request->get('parent_id',null);
        $attachmentsWithParentEmail = array();
        if(!empty($parentEmailId) && !empty ($recordId)) {
            $parentEmailModel = Vtiger_Record_Model::getInstanceById($parentEmailId);
            $attachmentsWithParentEmail = $parentEmailModel->getAttachmentDetails();
        }
        $existingAttachments = $request->get('attachments',array());
        if(empty($recordId)) {
			if(is_array($existingAttachments)) {
				foreach ($existingAttachments as $index =>  $existingAttachInfo) {
					$existingAttachInfo['tmp_name'] = $existingAttachInfo['name'];
					$existingAttachments[$index] = $existingAttachInfo;
					if(array_key_exists('docid',$existingAttachInfo)) {
						$documentIds[] = $existingAttachInfo['docid'];
						unset($existingAttachments[$index]);
					}

				}
			}
        }else{
            //If it is edit view unset the exising attachments
            //remove the exising attachments if it is in edit view

            $attachmentsToUnlink = array();
            $documentsToUnlink = array();


            foreach($attachmentsWithParentEmail as $i => $attachInfo) {
                $found = false;
                foreach ($existingAttachments as $index =>  $existingAttachInfo) {
                    if($attachInfo['fileid'] == $existingAttachInfo['fileid']) {
                        $found = true;
                        break;
                    }
                }
                //Means attachment is deleted
                if(!$found) {
                    if(array_key_exists('docid',$attachInfo)) {
                        $documentsToUnlink[] = $attachInfo['docid'];
                    }else{
                        $attachmentsToUnlink[] = $attachInfo;
                    }
                }
                unset($attachmentsWithParentEmail[$i]);
            }
            //Make the attachments as empty for edit view since all the attachments will already be there
            $existingAttachments = array();
            if(!empty($documentsToUnlink)) {
                $recordModel->deleteDocumentLink($documentsToUnlink);
            }

            if(!empty($attachmentsToUnlink)){
                $recordModel->deleteAttachment($attachmentsToUnlink);
            }

        }


		// This will be used for sending mails to each individual
		$toMailInfo = $request->get('toemailinfo');

		$to = $request->get('to');
		if(is_array($to)) {
			$to = implode(',',$to);
		}


		$recordModel->set('description', $request->get('description'));
		$recordModel->set('subject', $request->get('subject'));
        $recordModel->set('toMailNamesList',$request->get('toMailNamesList'));
		$recordModel->set('saved_toid', $to);
		$recordModel->set('ccmail', $request->get('cc'));
		$recordModel->set('bccmail', $request->get('bcc'));
		$recordModel->set('assigned_user_id', $currentUserModel->getId());
		$recordModel->set('email_flag', $flag);
		$recordModel->set('documentids', $documentIds);

		$recordModel->set('toemailinfo', $toMailInfo);
		foreach($toMailInfo as $recordId=>$emailValueList) {
			if($recordModel->getEntityType($recordId) == 'Users'){
				$parentIds .= $recordId.'@-1|';
			}else{
				$parentIds .= $recordId.'@1|';
			}
		}
		$recordModel->set('parent_id', $parentIds);

		//save_module still depends on the $_REQUEST, need to clean it up
		$_REQUEST['parent_id'] = $parentIds;

		$success = false;
		$viewer = $this->getViewer($request);
		if ($recordModel->checkUploadSize($documentIds)) {
			$recordModel->save();

            //To Handle existing attachments
            $current_user = Users_Record_Model::getCurrentUserModel();
            $ownerId = $recordModel->get('assigned_user_id');
            $date_var = date("Y-m-d H:i:s");
			if(is_array($existingAttachments)) {
				foreach ($existingAttachments as $index =>  $existingAttachInfo) {
					$file_name = $existingAttachInfo['attachment'];
					$path = $existingAttachInfo['path'];
					$fileId = $existingAttachInfo['fileid'];

					$oldFileName = $file_name;
					//SEND PDF mail will not be having file id
					if(!empty ($fileId)) {
						$oldFileName = $existingAttachInfo['fileid'].'_'.$file_name;
					}
					$oldFilePath = $path.'/'.$oldFileName;

					$binFile = sanitizeUploadFileName($file_name, $upload_badext);

					$current_id = $adb->getUniqueID("vtiger_crmentity");

					$filename = ltrim(basename(" " . $binFile)); //allowed filename like UTF-8 characters
					$filetype = $existingAttachInfo['type'];
					$filesize = $existingAttachInfo['size'];

					//get the file path inwhich folder we want to upload the file
					$upload_file_path = decideFilePath();
					$newFilePath = $upload_file_path . $current_id . "_" . $binFile;

					copy($oldFilePath, $newFilePath);

					$sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
					$params1 = array($current_id, $current_user->getId(), $ownerId, $moduleName . " Attachment", $recordModel->get('description'), $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
					$adb->pquery($sql1, $params1);

					$sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
					$params2 = array($current_id, $filename, $recordModel->get('description'), $filetype, $upload_file_path);
					$result = $adb->pquery($sql2, $params2);

					$sql3 = 'insert into vtiger_seattachmentsrel values(?,?)';
					$adb->pquery($sql3, array($recordModel->getId(), $current_id));
				}
			}
			$success = true;
			if($flag == 'SENT') {
				$status = $recordModel->send();
				if ($status === true) {
					// This is needed to set vtiger_email_track table as it is used in email reporting
					$recordModel->setAccessCountValue();
				} else {
					$success = false;
					$message = $status;
				}
			}

		} else {
			$message = vtranslate('LBL_MAX_UPLOAD_SIZE', $moduleName).' '.vtranslate('LBL_EXCEEDED', $moduleName);
		}
		$viewer->assign('SUCCESS', $success);
		$viewer->assign('MESSAGE', $message);
        $loadRelatedList = $request->get('related_load');
        if(!empty($loadRelatedList)){
            $viewer->assign('RELATED_LOAD',true);
        }
		$viewer->view('SendEmailResult.tpl', $moduleName);
	}

	/**
	 * Function returns the record Ids selected in the current filter
	 * @param Vtiger_Request $request
	 * @return integer
	 */
	public function getRecordsListFromRequest(Vtiger_Request $request) {
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}

		if($selectedIds == 'all'){
			$sourceRecord = $request->get('sourceRecord');
			$sourceModule = $request->get('sourceModule');
			if ($sourceRecord && $sourceModule) {
				$sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
				return $sourceRecordModel->getSelectedIdsList($request->get('parentModule'), $excludedIds);
			}

			$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
			if($customViewModel) {
				$searchKey = $request->get('search_key');
				$searchValue = $request->get('search_value');
				$operator = $request->get('operator');
                if(!empty($operator)) {
                    $customViewModel->set('operator', $operator);
                    $customViewModel->set('search_key', $searchKey);
                    $customViewModel->set('search_value', $searchValue);
                }
				return $customViewModel->getRecordIds($excludedIds);
			}
		}
        return array();
	}
}
