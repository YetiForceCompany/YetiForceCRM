<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Emails_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl() {
		list($parentId, $status) = explode('@', reset(array_filter(explode('|', $this->get('parent_id')))));
		return 'Javascript:Vtiger_Index_Js.showEmailPreview("'.$this->getId().'","'.$parentId.'")';
	}

	/**
	 * Function to save an Email
	 */
	public function save() {
            //Opensource fix for MailManager data mail attachment
		if($this->get('email_flag')!="MailManager"){ 
                    $this->set('date_start', date('Y-m-d')); 
                    $this->set('time_start', date('H:i')); 
                }
		$this->set('activitytype', 'Emails');

		//$currentUserModel = Users_Record_Model::getCurrentUserModel();
		//$this->set('assigned_user_id', $currentUserModel->getId());
		$this->getModule()->saveRecord($this);
		$documentIds = $this->get('documentids');
		if (!empty ($documentIds)) {
            $this->deleteDocumentLink();
			$this->saveDocumentDetails();
		}
	}

	/**
	 * Function sends mail
	 */
	public function send() {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$rootDirectory =  vglobal('root_directory');

		$mailer = Emails_Mailer_Model::getInstance();
		$mailer->IsHTML(true);

		$fromEmail = $this->getFromEmailAddress();
		$replyTo = $currentUserModel->get('email1');
		$userName = $currentUserModel->getName();

		// To eliminate the empty value of an array
		$toEmailInfo = array_filter($this->get('toemailinfo'));
        $toMailNamesList = array_filter($this->get('toMailNamesList'));
        foreach($toMailNamesList as $id => $emailData){
            foreach($emailData as $key => $email){
                if($toEmailInfo[$id]){
                    array_push($toEmailInfo[$id], $email['value']);
                }
            }
        }
        $emailsInfo = array();
		foreach ($toEmailInfo as $id => $emails) {
            foreach($emails as $key => $value){
                array_push($emailsInfo, $value);
            }
		}

        $toFieldData = array_diff(explode(',', $this->get('saved_toid')), $emailsInfo);
		$toEmailsData = array();
		$i = 1;
		foreach ($toFieldData as $value) {
			$toEmailInfo['to'.$i++] = array($value);
		}
		$attachments = $this->getAttachmentDetails();
		$status = false;

		// Merge Users module merge tags based on current user.
		$mergedDescription = getMergedDescription($this->get('description'), $currentUserModel->getId(), 'Users');

		foreach($toEmailInfo as $id => $emails) {
			$mailer->reinitialize();
			$mailer->ConfigSenderInfo($fromEmail, $userName, $replyTo);
			$old_mod_strings = vglobal('mod_strings');
			$description = $this->get('description');

			$parentModule = $this->getEntityType($id);
			if ($parentModule) {
			$currentLanguage = Vtiger_Language_Handler::getLanguage();
			$moduleLanguageStrings = Vtiger_Language_Handler::getModuleStringsFromFile($currentLanguage,$parentModule);
			vglobal('mod_strings', $moduleLanguageStrings['languageStrings']);

			if ($parentModule != 'Users') {
				// Apply merge for non-Users module merge tags.
				$description = getMergedDescription($mergedDescription, $id, $parentModule);
			} else {
				// Re-merge the description for user tags based on actual user.
					$description = getMergedDescription($description, $id, 'Users');
					vglobal('mod_strings', $old_mod_strings);
				}
			}

			if (strpos($description, '$logo$')) {
				$description = str_replace('$logo$',"<img src='cid:logo' />", $description);
				$logo = true;
			}

			foreach($emails as $email) {
				$mailer->Body = '';
				if ($parentModule) {
					$mailer->Body = $this->getTrackImageDetails($id, $this->isEmailTrackEnabled());
				}
				$mailer->Body .= $description;
				$mailer->Signature = str_replace(array('\r\n', '\n'),'<br>',$currentUserModel->get('signature'));
				if($mailer->Signature != '') {
					$mailer->Body.= '<br><br>'.decode_html($mailer->Signature);
				}
				$mailer->Subject = $this->get('subject');
				$mailer->AddAddress($email);

				//Adding attachments to mail
				if(is_array($attachments)) {
					foreach($attachments as $attachment) {
						$fileNameWithPath = $rootDirectory.$attachment['path'].$attachment['fileid']."_".$attachment['attachment'];
						if(is_file($fileNameWithPath)) {
							$mailer->AddAttachment($fileNameWithPath, $attachment['attachment']);
						}
					}
				}
				if ($logo) {
					//While sending email template and which has '$logo$' then it should replace with company logo
					$mailer->AddEmbeddedImage(dirname(__FILE__).'/../../../layouts/vlayout/skins/images/logo_mail.jpg', 'logo', 'logo.jpg', 'base64', 'image/jpg');
				}

				$ccs = array_filter(explode(',',$this->get('ccmail')));
				$bccs = array_filter(explode(',',$this->get('bccmail')));

				if(!empty($ccs)) {
					foreach($ccs as $cc) $mailer->AddCC($cc);
				}
				if(!empty($bccs)) {
					foreach($bccs as $bcc) $mailer->AddBCC($bcc);
				}
			}
			$status = $mailer->Send(true);
			if(!$status) {
				$status = $mailer->getError();
			} else {
                $mailString=$mailer->getMailString();
                $mailBoxModel = MailManager_Mailbox_Model::activeInstance();
                $folderName = $mailBoxModel->folder();
                if(!empty($folderName) && !empty($mailString)) {
                    $connector = MailManager_Connector_Connector::connectorWithModel($mailBoxModel, '');
                    imap_append($connector->mBox, $connector->mBoxUrl.$folderName, $mailString, "\\Seen");
                }
            }
		}
		return $status;
	}

	/**
	 * Returns the From Email address that will be used for the sent mails
	 * @return <String> - from email address
	 */
	function getFromEmailAddress() {
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		$fromEmail = false;
		$result = $db->pquery('SELECT from_email_field FROM vtiger_systems WHERE server_type=?', array('email'));
		if ($db->num_rows($result)) {
			$fromEmail = decode_html($db->query_result($result, 0, 'from_email_field'));
		}
		if (empty($fromEmail)) $fromEmail = $currentUserModel->get('email1');
		return $fromEmail;
	}

	/**
	 * Function returns the attachment details for a email
	 * @return <Array> List of attachments
	 */
	function getAttachmentDetails() {
		$db = PearDatabase::getInstance();

		$attachmentRes = $db->pquery("SELECT * FROM vtiger_attachments
						INNER JOIN vtiger_seattachmentsrel ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid
						WHERE vtiger_seattachmentsrel.crmid = ?", array($this->getId()));
		$numOfRows = $db->num_rows($attachmentRes);
        $attachmentsList = array();
		if($numOfRows) {
			for($i=0; $i<$numOfRows; $i++) {
				$attachmentsList[$i]['fileid'] = $db->query_result($attachmentRes, $i, 'attachmentsid');
				$attachmentsList[$i]['attachment'] = decode_html($db->query_result($attachmentRes, $i, 'name'));
                $path = $db->query_result($attachmentRes, $i, 'path');
				$attachmentsList[$i]['path'] = $path;
                $attachmentsList[$i]['size'] = filesize($path.$attachmentsList[$i]['fileid'].'_'.$attachmentsList[$i]['attachment']);
                $attachmentsList[$i]['type'] = $db->query_result($attachmentRes, $i, 'type');
			}
		}

		$documentsList = $this->getRelatedDocuments();

        //Attachments are getting duplicated when forwarding a mail in Mail Manager.
		if($documentsList) {
			foreach ($documentsList as $document) {
				$flag = false;
				foreach ($attachmentsList as $attachment) {
					if($attachment['fileid'] == $document['fileid']) {
						$flag = true;
						break;
					}
				}
				if(!$flag) $attachmentsList[] = $document;
			}
		}

		return $attachmentsList;
	}

	/**
	 * Function returns the document details for a email
	 * @return <Array> List of Documents
	 */
	public function getRelatedDocuments() {
		$db = PearDatabase::getInstance();

		$documentRes = $db->pquery("SELECT * FROM vtiger_senotesrel
						INNER JOIN vtiger_crmentity ON vtiger_senotesrel.notesid = vtiger_crmentity.crmid AND vtiger_senotesrel.crmid = ?
						INNER JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_senotesrel.notesid
						INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid = vtiger_notes.notesid
						INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid
						WHERE vtiger_crmentity.deleted = 0", array($this->getId()));
		$numOfRows = $db->num_rows($documentRes);

		if($numOfRows) {
			for($i=0; $i<$numOfRows; $i++) {
				$documentsList[$i]['name'] = $db->query_result($documentRes, $i, 'filename');
				$filesize = $db->query_result($documentRes, $i, 'filesize');
				$documentsList[$i]['size'] = $this->getFormattedFileSize($filesize);
				$documentsList[$i]['docid'] = $db->query_result($documentRes, $i, 'notesid');
				$documentsList[$i]['path'] = $db->query_result($documentRes, $i, 'path');
				$documentsList[$i]['fileid'] = $db->query_result($documentRes, $i, 'attachmentsid');
				$documentsList[$i]['attachment'] = $db->query_result($documentRes, $i, 'name');
                $documentsList[$i]['type'] = $db->query_result($documentRes, $i, 'type');
			}
		}
		return $documentsList;
	}

	/**
	 * Function to get File size
	 * @param <Integer> $filesize
	 * @return <String> filesize
	 */
	public function getFormattedFileSize($filesize) {
		if($filesize < 1024) {
			$filesize = sprintf("%0.2f",round($filesize, 2)).'B';
		} else if($filesize > 1024 && $filesize < 1048576) {
			$filesize = sprintf("%0.2f",round($filesize/1024, 2)).'KB';
		} else if($filesize > 1048576) {
			$filesize = sprintf("%0.2f",round($filesize/(1024*1024), 2)).'MB';
		}
		return $filesize;
	}

	/**
	 * Function to save details of document and email
	 */
	public function saveDocumentDetails() {
		$db = PearDatabase::getInstance();
		$record = $this->getId();

		$documentIds = array_unique($this->get('documentids'));

		$count = count($documentIds);
		for ($i=0; $i<$count; $i++) {
			$db->pquery("INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?, ?)", array($record, $documentIds[$i]));
		}
	}

    /**
     * Function which will remove all the exising document links with email
     * @param <Array> $idList - array of ids
     */
    public function deleteDocumentLink($idList = array()){
        $db = PearDatabase::getInstance();
        $query =  'DELETE FROM vtiger_senotesrel where crmid=?';
        $params = array($this->getId());
        if(count($idList) > 0) {
            $query .= 'AND notesid IN ('.generateQuestionMarks($idList).')';
            $params = array_merge($params,$idList);
        }
        $db->pquery($query,$params);
    }

    /**
     * Function which will delete the existing attachments for the emails
     * @param <Array> $emailAttachmentDetails - array of value which will be having fileid key as attachement id which need to be deleted
     */
    public function deleteAttachment($emailAttachmentDetails = array()) {
        $db = PearDatabase::getInstance();

        if(count($emailAttachmentDetails) <= 0) {
            return;
        }
        $attachmentIdList = array();
        foreach($emailAttachmentDetails as $index => $attachInfo){
            $attachmentIdList[] = $attachInfo['fileid'];
        }

        $db->pquery('UPDATE vtiger_crmentity SET deleted=0 WHERE crmid IN('.generateQuestionMarks($attachmentIdList).')',$attachmentIdList);
        $db->pquery('DELETE FROM vtiger_attachments WHERE attachmentsid IN('.generateQuestionMarks($attachmentIdList).')',$attachmentIdList);
        $db->pquery('DELETE FROM vtiger_seattachmentsrel WHERE crmid=? and attachmentsid IN('.generateQuestionMarks($attachmentIdList).')',
                array_merge(array($this->getId()),$attachmentIdList));

    }

	/**
	 * Function to check the total size of files is morethan max upload size or not
	 * @param <Array> $documentIds
	 * @return <Boolean> true/false
	 */
	public function checkUploadSize($documentIds = false) {
		$totalFileSize = 0;
		if (!empty ($_FILES)) {
			foreach ($_FILES as $fileDetails) {
				$totalFileSize = $totalFileSize + (int) $fileDetails['size'];
			}
		}
		if (!empty ($documentIds)) {
			$count = count($documentIds);
			for ($i=0; $i<$count; $i++) {
				$documentRecordModel = Vtiger_Record_Model::getInstanceById($documentIds[$i], 'Documents');
				$totalFileSize = $totalFileSize + (int) $documentRecordModel->get('filesize');
			}
		}

		if ($totalFileSize > vglobal('upload_maxsize')) {
			return false;
		}
		return true;
	}

	/**
	 * Function to get Track image details
	 * @param <Integer> $crmId
	 * @param <boolean> $emailTrack true/false
	 * @return <String>
	 */
	public function getTrackImageDetails($crmId, $emailTrack = true) {
		$siteURL = vglobal('site_URL');
		$applicationKey = vglobal('application_unique_key');
		$emailId = $this->getId();

		$trackURL = "$siteURL/modules/Emails/actions/TrackAccess.php?record=$emailId&parentId=$crmId&applicationKey=$applicationKey";
		$imageDetails = "<img src='$trackURL' alt='' width='1' height='1'>";
		return $imageDetails;
	}


	/**
	 * Function check email track enabled or not
	 * @return <boolean> true/false
	 */
	public function isEmailTrackEnabled() {
		//In future this track will be coming from client side/User preferences
		return true;
	}

	/**
	 * Function to update Email track details
	 * @param <String> $parentId
	 */
	public function updateTrackDetails($parentId) {
		$db = PearDatabase::getInstance();
		$recordId = $this->getId();

		$db->pquery("INSERT INTO vtiger_email_access(crmid, mailid, accessdate, accesstime) VALUES(?, ?, ?, ?)", array($parentId, $recordId, date('Y-m-d'), date('Y-m-d H:i:s')));

		$result = $db->pquery("SELECT 1 FROM vtiger_email_track WHERE crmid = ? AND mailid = ?", array($parentId, $recordId));
		if ($db->num_rows($result)>0) {
			$db->pquery("UPDATE vtiger_email_track SET access_count = access_count+1 WHERE crmid = ? AND mailid = ?", array($parentId, $recordId));
		} else {
			$db->pquery("INSERT INTO vtiger_email_track(crmid, mailid, access_count) values(?, ?, ?)", array($parentId, $recordId, 1));
		}
	}

	/**
	 * Function to set Access count value by default as 0
	 */
	public function setAccessCountValue() {
		$record = $this->getId();
		$moduleName = $this->getModuleName();

		$focus = new $moduleName();
		$focus->setEmailAccessCountValue($record);
	}

	/**
	 * Function to get Access count value
	 * @param <String> $parentId
	 * @return <String>
	 */
	public function getAccessCountValue($parentId) {
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT access_count FROM vtiger_email_track WHERE crmid = ? AND mailid = ?", array($parentId, $this->getId()));
		return $db->query_result($result, 0, 'access_count');
	}

	/**
	 * Function checks if the mail is sent or not
	 * @return <Boolean>
	 */
	public function isSentMail(){
		if(!array_key_exists('email_flag', $this->getData())){
			$db = PearDatabase::getInstance();
			$query = 'SELECT email_flag FROM vtiger_emaildetails WHERE emailid=?';
			$result = $db->pquery($query,array($this->getId()));
			if($db->num_rows($result)>0) {
				$this->set('email_flag',$db->query_result($result,0,'email_flag'));
			} else {
				//If not row exits then make it as false
				return false;
			}
		}
		if($this->get('email_flag') == "SENT"){
			return true;
		}
		return false;
	}

        //Opensource fix for data updation for mail attached from mailmanager
        public function isFromMailManager(){ 
            if(!array_key_exists('email_flag', $this->getData())){ 
                    $db = PearDatabase::getInstance(); 
                    $query = 'SELECT email_flag FROM vtiger_emaildetails WHERE emailid=?'; 
                    $result = $db->pquery($query,array($this->getId())); 
                    if($db->num_rows($result)>0) { 
                            $this->set('email_flag',$db->query_result($result,0,'email_flag')); 
                    } else { 
                            //If not row exits then make it as false 
                            return false; 
                    } 
            } 
            if($this->get('email_flag') == "MailManager"){ 
                    return true; 
            } 
            return false; 
        } 
	function getEntityType($id) {
		$db = PearDatabase::getInstance();
		$moduleModel = $this->getModule();
		$emailRelatedModules = $moduleModel->getEmailRelatedModules();
		$relatedModule = '';
		if (!empty($id)) {
			$sql = "SELECT setype FROM vtiger_crmentity WHERE crmid=?";
			$result = $db->pquery($sql, array($id));
			$relatedModule = $db->query_result($result, 0, "setype");

			if(!in_array($relatedModule, $emailRelatedModules)){
				$sql = 'SELECT id FROM vtiger_users WHERE id=?';
				$result = $db->pquery($sql, array($id));
				if($db->num_rows($result) > 0){
					$relatedModule = 'Users';
				}
			}
		}
		return $relatedModule;
	}
}
