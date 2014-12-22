<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Faq_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function to get Instance of Faq Record Model using TroubleTicket RecordModel
	 * @param  HelpDesk_Record_Model
	 * @return Faq_Record_Model
	 */
	public static function getInstanceFromHelpDesk($parentRecordModel) {
		$recordModel = Vtiger_Record_Model::getCleanInstance('Faq');
		$fieldMappingList = Faq_Record_Model::getTicketToFAQMappingFields();

		foreach ($fieldMappingList as $fieldMapping) {
			$ticketField = $fieldMapping['ticketField'];
			$faqField = $fieldMapping['faqField'];
			if (!empty ($ticketField)) {
				$faqData[$faqField] = $parentRecordModel->get($ticketField);
			} else {
				$faqData[$faqField] = $fieldMapping['defaultValue'];
			}
		}
		$recordModel->setData($faqData);

		//Updating the answer of Faq
		$answer = $recordModel->get('faq_answer');
		if ($answer) {
			$answer = vtranslate('LBL_SOLUTION', 'Faq').":\r\n".$answer;
		}

		$commentsList = $parentRecordModel->getCommentsList();
		if ($commentsList) {
			$answer .= "\r\n\r\n".vtranslate('LBL_COMMENTS', 'Faq').":";
			foreach ($commentsList as $comment) {
				$answer .= "\r\n$comment";
			}
		}
		$recordModel->set('faq_answer', $answer);
		return $recordModel;
	}

	/**
	 * Function get List of Fields which are mapping from Truoble Tickets to FAQ
	 * @return <array>
	 */
	public static function getTicketToFAQMappingFields() {
		return array(
				array('ticketField'=>'ticket_title', 'faqField'=>'question', 'defaultValue'=>''),
				array('ticketField'=>'product_id', 'faqField'=>'product_id', 'defaultValue'=>''),
				array('ticketField'=>'solution', 'faqField'=>'faq_answer', 'defaultValue'=>''),
				array('ticketField'=>'', 'faqField'=>'faqcategories', 'defaultValue'=>'General'),
				array('ticketField'=>'', 'faqField'=>'faqstatus', 'defaultValue'=>'Draft')
		);
	}
}