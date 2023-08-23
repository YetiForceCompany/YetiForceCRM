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

class Calendar_DetailView_Model extends Vtiger_DetailView_Model
{
	/** {@inheritdoc} */
	public function getDetailViewLinks(array $linkParams): array
	{
		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$recordId = $recordModel->getId();
		if ($recordModel->isEditable() && $this->getModule()->isPermitted('DetailView') && \App\Privilege::isPermitted($moduleName, 'ActivityComplete', $recordId) && \App\Privilege::isPermitted($moduleName, 'ActivityCancel', $recordId) && \App\Privilege::isPermitted($moduleName, 'ActivityPostponed', $recordId) && \in_array($recordModel->get('activitystatus'), Calendar_Module_Model::getComponentActivityStateLabel('current'))) {
			$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_BASIC',
				'linklabel' => 'LBL_SET_RECORD_STATUS',
				'linkurl' => '#',
				'linkdata' => ['url' => $recordModel->getActivityStateModalUrl()],
				'linkicon' => 'fas fa-check',
				'linkclass' => 'btn-outline-dark btn-sm showModal closeCalendarRekord',
			]);
		}
		if ($recordModel->isEditable() && \App\Mail::checkInternalMailClient()) {
			$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_ADDITIONAL',
				'linklabel' => 'LBL_SEND_CALENDAR',
				'linkdata' => ['url' => "index.php?module={$moduleName}&view=SendInvitationModal&record={$recordId}"],
				'linkicon' => 'yfi-send-invitation',
				'linkclass' => 'btn-outline-dark btn-sm js-show-modal',
			]);
		}
		if (!$recordModel->isReadOnly() && !$recordModel->isEmpty('location') && App\Privilege::isPermitted('OpenStreetMap')) {
			$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_BASIC',
				'linklabel' => 'LBL_SHOW_LOCATION',
				'linkurl' => 'javascript:Vtiger_Index_Js.showLocation(this)',
				'linkdata' => ['location' => $recordModel->getDisplayValue('location')],
				'linkicon' => 'fas fa-map-marker-alt',
				'linkclass' => 'btn-outline-dark btn-sm'
			]);
		}
		if (!$recordModel->isReadOnly() && $recordModel->privilegeToMoveToTrash() && 1 === $recordModel->get('reapeat')) {
			$stateColors = App\Config::search('LIST_ENTITY_STATE_COLOR');
			foreach ($linkModelList['DETAIL_VIEW_EXTENDED'] as $key => $linkObject) {
				if ('LBL_MOVE_TO_TRASH' == $linkObject->linklabel) {
					unset($linkModelList['DETAIL_VIEW_EXTENDED'][$key]);
				}
			}
			$linkModelList['DETAIL_VIEW_EXTENDED'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_EXTENDED',
				'linklabel' => 'LBL_MOVE_TO_TRASH',
				'dataUrl' => 'index.php?module=' . $recordModel->getModuleName() . '&action=State&state=Trash&record=' . $recordModel->getId(),
				'linkdata' => ['confirm' => \App\Language::translate('LBL_MOVE_TO_TRASH_DESC')],
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'entityStateBtn btn-outline-dark btn-sm js-record-action',
				'style' => empty($stateColors['Trash']) ? '' : "background: {$stateColors['Trash']};",
			]);
		}
		if (!$recordModel->isReadOnly() && $recordModel->privilegeToDelete() && 1 === $recordModel->get('reapeat')) {
			foreach ($linkModelList['DETAIL_VIEW_EXTENDED'] as $key => $linkObject) {
				if ('LBL_DELETE_RECORD_COMPLETELY' == $linkObject->linklabel) {
					unset($linkModelList['DETAIL_VIEW_EXTENDED'][$key]);
				}
			}
			$linkModelList['DETAIL_VIEW_EXTENDED'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_EXTENDED',
				'linklabel' => 'LBL_DELETE_RECORD_COMPLETELY',
				'dataUrl' => 'index.php?module=' . $recordModel->getModuleName() . '&action=Delete&record=' . $recordModel->getId(),
				'linkdata' => ['confirm' => \App\Language::translate('LBL_DELETE_RECORD_COMPLETELY_DESC')],
				'linkicon' => 'fas fa-eraser',
				'linkclass' => 'btn-dark btn-sm js-record-action',
			]);
		}
		return $linkModelList;
	}
}
