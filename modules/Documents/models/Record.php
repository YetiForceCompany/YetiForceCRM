<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Documents_Record_Model extends Vtiger_Record_Model
{

	public function getDownloadFileURL()
	{
		if ($this->get('filelocationtype') == 'I') {
			$fileDetails = $this->getFileDetails();
			return 'index.php?module=' . $this->getModuleName() . '&action=DownloadFile&record=' . $this->getId() . '&fileid=' . $fileDetails['attachmentsid'];
		} else {
			return $this->get('filename');
		}
	}

	public function checkFileIntegrityURL()
	{
		return "javascript:Documents_Detail_Js.checkFileIntegrity('index.php?module=" . $this->getModuleName() . "&action=CheckFileIntegrity&record=" . $this->getId() . "')";
	}

	public function checkFileIntegrity()
	{
		$returnValue = false;
		if ($this->get('filelocationtype') === 'I') {
			$fileDetails = $this->getFileDetails();
			if (!empty($fileDetails)) {
				$savedFile = $fileDetails['path'] . $fileDetails['attachmentsid'] . '_' . $fileDetails['name'];
				if (file_exists($savedFile) && fopen($savedFile, 'r')) {
					$returnValue = true;
				}
			}
		}
		return $returnValue;
	}

	public function getFileDetails()
	{
		return (new \App\Db\Query())->from('vtiger_attachments')
				->innerJoin('vtiger_seattachmentsrel', 'vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid')
				->where(['crmid' => $this->get('id')])
				->one();
	}

	public function downloadFile()
	{
		$fileDetails = $this->getFileDetails();
		$fileContent = false;

		if (!empty($fileDetails)) {
			$filePath = $fileDetails['path'];
			$fileName = $fileDetails['name'];

			if ($this->get('filelocationtype') == 'I') {
				$fileName = html_entity_decode($fileName, ENT_QUOTES, vglobal('default_charset'));
				$savedFile = $fileDetails['attachmentsid'] . "_" . $fileName;

				$fileSize = filesize($filePath . $savedFile);
				$fileSize = $fileSize + ($fileSize % 1024);

				if (fopen($filePath . $savedFile, "r")) {
					$fileContent = fread(fopen($filePath . $savedFile, "r"), $fileSize);
					$fileName = $this->get('filename');
					header("Content-type: " . $fileDetails['type']);
					header("Pragma: public");
					header("Cache-Control: private");
					header("Content-Disposition: attachment; filename=\"$fileName\"");
					header("Content-Description: PHP Generated Data");
				}
			}
		}
		echo $fileContent;
	}

	public function updateFileStatus($status)
	{
		App\Db::getInstance()->createCommand()->update('vtiger_notes', ['filestatus' => $status], ['notesid' => $this->get('id')])->execute();
	}

	public function updateDownloadCount()
	{
		$db = PearDatabase::getInstance();
		$notesId = $this->get('id');

		$result = $db->pquery("SELECT filedownloadcount FROM vtiger_notes WHERE notesid = ?", array($notesId));
		$downloadCount = $db->query_result($result, 0, 'filedownloadcount') + 1;

		$db->pquery("UPDATE vtiger_notes SET filedownloadcount = ? WHERE notesid = ?", array($downloadCount, $notesId));
	}

	public function getDownloadCountUpdateUrl()
	{
		return "index.php?module=Documents&action=UpdateDownloadCount&record=" . $this->getId();
	}

	public static function getReferenceModuleByDocId($record)
	{
		$db = PearDatabase::getInstance();
		$sql = 'SELECT DISTINCT vtiger_crmentity.setype 
			   FROM vtiger_crmentity INNER JOIN vtiger_senotesrel 
				   ON vtiger_senotesrel.crmid = vtiger_crmentity.crmid 
			   WHERE vtiger_crmentity.deleted = 0 
				 AND vtiger_senotesrel.notesid = ?';
		$result = $db->pquery($sql, [$record]);
		return $db->getArrayColumn($result);
	}

	public static function getFileIconByFileType($fileType)
	{
		$fileIcon = \App\Layout\Icon::getIconByFileType($fileType);
		return $fileIcon;
	}

	/**
	 * The function decide about mandatory save record
	 * @return type
	 */
	public function isMandatorySave()
	{
		return $_FILES ? true : false;
	}

	/**
	 * Function to save record
	 */
	public function saveToDb()
	{
		parent::saveToDb();
		$db = \App\Db::getInstance();
		$fileNameByField = 'filename';
		if ($this->get('filelocationtype') === 'I') {
			if (!isset($this->file)) {
				$file = $_FILES[$fileNameByField];
			} else {
				$file = $this->file;
			}
			if (!empty($file['name'])) {
				$errCode = $file['error'];
				if ($errCode === 0) {
					$fileInstance = \App\Fields\File::loadFromRequest($file);
					if ($fileInstance->validate()) {
						$fileName = $file['name'];
						$fileName = \vtlib\Functions::fromHTML(preg_replace('/\s+/', '_', $fileName));
						$fileType = $file['type'];
						$fileSize = $file['size'];
						$fileLocationType = 'I';
						$fileName = ltrim(basename(" " . $fileName)); //allowed filename like UTF-8 characters
					}
				}
			} elseif ($this->get($fileNameByField)) {
				$fileName = $this->get($fileNameByField);
				$fileSize = $this->get('filesize');
				$fileType = $this->get('filetype');
				$fileLocationType = $this->get('filelocationtype');
				$fileDownloadCount = 0;
			} else {
				$fileLocationType = 'I';
				$fileType = '';
				$fileSize = 0;
				$fileDownloadCount = null;
			}
		} else if ($this->get('filelocationtype') === 'E') {
			$fileLocationType = 'E';
			$fileName = $this->get($fileNameByField);
			// If filename does not has the protocol prefix, default it to http://
			// Protocol prefix could be like (https://, smb://, file://, \\, smb:\\,...)
			if (!empty($fileName) && !preg_match('/^\w{1,5}:\/\/|^\w{0,3}:?\\\\\\\\/', trim($fileName), $match)) {
				$fileName = "http://$fileName";
			}
			$fileType = '';
			$fileSize = 0;
			$fileDownloadCount = null;
		}
		$db->createCommand()->update('vtiger_notes', ['filename' => decode_html($fileName), 'filesize' => $fileSize, 'filetype' => $fileType, 'filelocationtype' => $fileLocationType, 'filedownloadcount' => $fileDownloadCount], ['notesid' => $this->getId()])->execute();
		//Inserting into attachments table
		if ($fileLocationType === 'I') {
			if (!isset($this->file)) {
				$file = $_FILES[$fileNameByField];
			} else {
				$file = $this->file;
			}
			if ($file['name'] != '' && $file['size'] > 0) {
				$file['original_name'] = AppRequest::get('0_hidden');
				$this->uploadAndSaveFile($file);
			}
		} else {
			$db->createCommand()->delete('vtiger_seattachmentsrel', ['crmid' => $this->getId()])->execute();
		}
		//set the column_fields so that its available in the event handlers
		$this->set('filename', $fileName)->set('filesize', $fileSize)->set('filetype', $fileType)->set('filedownloadcount', $fileDownloadCount);
	}
}
