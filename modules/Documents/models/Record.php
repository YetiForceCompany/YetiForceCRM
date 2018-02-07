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

/**
 * Class Documents_Record_Model
 */
class Documents_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Get download file url
	 * @return string
	 */
	public function getDownloadFileURL()
	{
		if ($this->get('filelocationtype') == 'I') {
			$fileDetails = $this->getFileDetails();
			return 'file.php?module=' . $this->getModuleName() . '&action=DownloadFile&record=' . $this->getId() . '&fileid=' . $fileDetails['attachmentsid'];
		} else {
			return $this->get('filename');
		}
	}

	/**
	 * Check file integrity url
	 * @return string
	 */
	public function checkFileIntegrityURL()
	{
		return "javascript:Documents_Detail_Js.checkFileIntegrity('index.php?module=" . $this->getModuleName() . "&action=CheckFileIntegrity&record=" . $this->getId() . "')";
	}

	/**
	 * Check file integrity
	 * @return boolean
	 */
	public function checkFileIntegrity()
	{
		$returnValue = false;
		if ($this->get('filelocationtype') === 'I') {
			$fileDetails = $this->getFileDetails();
			if (!empty($fileDetails)) {
				$savedFile = $fileDetails['path'] . $fileDetails['attachmentsid'];
				if (file_exists($savedFile) && fopen($savedFile, 'r')) {
					$returnValue = true;
				}
			}
		}
		return $returnValue;
	}

	/**
	 * Get file details
	 * @return array
	 */
	public function getFileDetails()
	{
		return (new \App\Db\Query())->from('vtiger_attachments')
				->innerJoin('vtiger_seattachmentsrel', 'vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid')
				->where(['crmid' => $this->get('id')])
				->one();
	}

	/**
	 * Download file
	 */
	public function downloadFile()
	{
		$fileDetails = $this->getFileDetails();
		$fileContent = false;

		if (!empty($fileDetails)) {
			$filePath = $fileDetails['path'];
			$fileName = $fileDetails['name'];

			if ($this->get('filelocationtype') == 'I') {
				$fileName = html_entity_decode($fileName, ENT_QUOTES, \AppConfig::main('default_charset'));
				if (file_exists($filePath . $fileDetails['attachmentsid'])) {
					$savedFile = $fileDetails['attachmentsid'];
				} else {
					$savedFile = $fileDetails['attachmentsid'] . '_' . $fileName;
				}

				$fileSize = filesize($filePath . $savedFile);
				$fileSize = $fileSize + ($fileSize % 1024);

				if (fopen($filePath . $savedFile, 'r')) {
					$fileContent = fread(fopen($filePath . $savedFile, 'r'), $fileSize);
					$fileName = $this->get('filename');
					header('Content-type: ' . $fileDetails['type']);
					header('Pragma: public');
					header('Cache-Control: private');
					if ($this->get('show')) {
						header('Content-Disposition: inline');
					} else {
						header("Content-Disposition: attachment; filename=\"$fileName\"");
					}
					header('Content-Description: PHP Generated Data');
				}
			}
		}
		echo $fileContent;
	}

	/**
	 * Update file status
	 * @param int $status
	 */
	public function updateFileStatus($status)
	{
		App\Db::getInstance()->createCommand()->update('vtiger_notes', ['filestatus' => $status], ['notesid' => $this->get('id')])->execute();
	}

	/**
	 * Update download count
	 */
	public function updateDownloadCount()
	{
		$notesId = $this->get('id');
		$downloadCount = (new \App\Db\Query())->select(['filedownloadcount'])->from('vtiger_notes')->where(['notesid' => $notesId])->scalar();
		$downloadCount++;
		\App\Db::getInstance()->createCommand()->update('vtiger_notes', ['filedownloadcount' => $downloadCount], ['notesid' => $notesId]);
	}

	/**
	 * Get download count update url
	 * @return string
	 */
	public function getDownloadCountUpdateUrl()
	{
		return "index.php?module=Documents&action=UpdateDownloadCount&record=" . $this->getId();
	}

	/**
	 * Get reference module by doc id
	 * @param int $record
	 * @return array
	 */
	public static function getReferenceModuleByDocId($record)
	{
		return (new App\Db\Query())->select(['vtiger_crmentity.setype'])->from('vtiger_crmentity')->innerJoin('vtiger_senotesrel', 'vtiger_senotesrel.crmid = vtiger_crmentity.crmid')->where(['vtiger_crmentity.deleted' => 0, 'vtiger_senotesrel.notesid' => $record])->distinct()->column();
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
		$fileName = $fileType = '';
		$fileSize = 0;
		$fileDownloadCount = null;
		if ($this->get('filelocationtype') === 'I') {
			if (!isset($this->file)) {
				if (isset($_FILES[$fileNameByField])) {
					$file = $_FILES[$fileNameByField];
				} else {
					$file = null;
				}
			} else {
				$file = $this->file;
			}
			if (!empty($file['name'])) {
				if (isset($file['error']) && $file['error'] === 0) {
					$fileInstance = \App\Fields\File::loadFromRequest($file);
					if ($fileInstance->validate()) {
						$fileName = App\Purifier::purify($file['name']);
						$fileType = $fileInstance->getMimeType();
						$fileSize = $file['size'];
						$fileLocationType = 'I';
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
		}
		$db->createCommand()->update('vtiger_notes', ['filename' => App\Purifier::decodeHtml($fileName), 'filesize' => $fileSize, 'filetype' => $fileType, 'filelocationtype' => $fileLocationType, 'filedownloadcount' => $fileDownloadCount], ['notesid' => $this->getId()])->execute();
		//Inserting into attachments table
		if ($fileLocationType === 'I') {
			if (!isset($this->file)) {
				if (isset($_FILES[$fileNameByField])) {
					$file = $_FILES[$fileNameByField];
				} else {
					$file = null;
				}
			} else {
				$file = $this->file;
			}
			if ($file['name'] !== '' && $file['size'] > 0) {
				$file['original_name'] = \App\Request::_get('0_hidden');
				$this->uploadAndSaveFile($file);
			}
		} else {
			$db->createCommand()->delete('vtiger_seattachmentsrel', ['crmid' => $this->getId()])->execute();
		}
		//set the column_fields so that its available in the event handlers
		$this->set('filename', $fileName)->set('filesize', $fileSize)->set('filetype', $fileType)->set('filedownloadcount', $fileDownloadCount);
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete()
	{
		parent::delete();
		$dbCommand = \App\Db::getInstance()->createCommand();
		$attachmentsIds = (new \App\Db\Query())->select(['attachmentsid'])->from('vtiger_seattachmentsrel')->where(['crmid' => $this->getId()])->column();
		if (!empty($attachmentsIds)) {
			$dataReader = (new \App\Db\Query())->select(['path', 'attachmentsid'])->from('vtiger_attachments')->where(['attachmentsid' => $attachmentsIds])->createCommand()->query();
			while ($row = $dataReader->read()) {
				$fileName = $row['path'] . $row['attachmentsid'];
				if (file_exists($fileName)) {
					unlink($fileName);
				}
			}
			$dataReader->close();
			$dbCommand->delete('vtiger_seattachmentsrel', ['crmid' => $this->getId()])->execute();
			$dbCommand->delete('vtiger_attachments', ['attachmentsid' => $attachmentsIds])->execute();
			$dbCommand->delete('vtiger_crmentity', ['crmid' => $attachmentsIds])->execute();
		}
	}
}
