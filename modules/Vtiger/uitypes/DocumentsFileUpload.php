<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_DocumentsFileUpload_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/DocumentsFileUpload.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $value
	 * @param <Integer> $record
	 * @param <Vtiger_Record_Model>
	 * @return string
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		if ($recordInstance) {
			$fileLocationType = $recordInstance->get('filelocationtype');
			$fileStatus = $recordInstance->get('filestatus');
			if (!empty($value) && $fileStatus) {
				if ($fileLocationType == 'I') {
					$fileId = (new App\Db\Query())->select(['attachmentsid'])
						->from('vtiger_seattachmentsrel')
						->where(['crmid' => $record])
						->scalar();
					if ($fileId) {
						$value = '<a href="index.php?module=Documents&action=DownloadFile&record=' . $record . '&fileid=' . $fileId . '"' .
							' title="' . vtranslate('LBL_DOWNLOAD_FILE', 'Documents') . '" >' . $value . '</a>';
					}
				} else {
					$value = '<a href="' . $value . '" target="_blank" title="' . vtranslate('LBL_DOWNLOAD_FILE', 'Documents') . '" >' . $value . '</a>';
				}
			}
		}
		return $value;
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if ($value === null) {
			$fileName = (new App\Db\Query())->select(['filename'])->from('vtiger_notes')->where(['notesid' => $this->id])->one();
			if ($fileName) {
				return decode_html($fileName);
			}
			return $value;
		} else {
			return decode_html($value);
		}
	}
}
