<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class VTSimpleTemplate
{

	/**
	 * @var Vtiger_Record_Model 
	 */
	private $recordModel;

	function __construct($templateString)
	{
		$this->template = $templateString;
	}

	function render($recordModel)
	{
		$this->recordModel = $recordModel;
		return $this->parseTemplate();
	}

	private function matchHandler($match)
	{
		preg_match('/\((\w+) : \(([_\w]+)\) (\w+)\)/', $match[1], $matches);
		if ($match[1] == 'logo') {
			return $this->getMetaValue($match[1]);
		}
		// If parent is empty then we can't do any thing here
		if ($this->recordModel) {
			if (count($matches) === 0) {
				$fieldname = $match[1];
				//To handle comments for this module
				if (in_array($fieldname, array('lastComment', 'last5Comments', 'allComments'))) {
					return $this->getComments($this->recordModel->getModuleName(), $fieldname, $this->recordModel->getId());
				}
				if (!$this->recordModel->isEmpty($fieldname) || $fieldname === '_DATE_FORMAT_') {
					if ($this->useValue($fieldname, $this->recordModel->getModuleName())) {
						$result = $this->recordModel->getDisplayValue($fieldname);
					} else {
						$result = '';
					}
				} else {
					$result = '$' . $fieldname;
				}
			} else {
				list($full, $referenceField, $referenceModule, $fieldname) = $matches;
				if ($referenceModule === '__VtigerMeta__' || $fieldname === 'dbLabel') {
					$result = $this->getMetaValue($fieldname);
				} else if ('__VtigerCompany__' === $referenceModule) {
					$result = $this->getCompanySetting($fieldname);
				} else {
					$referenceId = $this->recordModel->get($referenceField);
					$referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModule);
					$fieldModel = Vtiger_Field_Model::getInstance($fieldname, $referenceModuleModel);
					if ($referenceId === null) {
						$result = '';
					} else {
						//To handle comments for this reference module
						if (in_array($fieldname, array('lastComment', 'last5Comments', 'allComments'))) {
							return $this->getComments($referenceModule, $fieldname, $referenceId);
						}
						if ($referenceField === 'contact_id') {
							$referenceIdsList = explode(',', $referenceId);
							$parts = array();
							foreach ($referenceIdsList as $referenceId) {
								$referenceRecordModel = Vtiger_Record_Model::getInstanceById($referenceId);
								if ($this->useValue($fieldname, $referenceModule)) {
									$parts[] = $referenceRecordModel->getDisplayValue($fieldname);
								}
							}
							return implode(',', $parts);
						}
						if ($fieldModel->getFieldDataType() === 'owner') {
							/*
							  require_once('include/utils/GetGroupUsers.php');
							  $ggu = new GetGroupUsers();
							  $ggu->getAllUsersInGroup($referenceId);

							  $users = $ggu->group_users;
							  $parts = Array();
							  foreach ($users as $userId) {
							  $refId = vtws_getWebserviceEntityId("Users", $userId);
							  $entity = $this->cache->forId($refId);
							  $data = $entity->getData();
							  if ($this->useValue($data, $fieldname, $referenceModule)) {
							  $parts[] = $this->transformToUserFormat($referenceModule, $fieldname, $data[$fieldname]);
							  }
							  }
							  $result = implode(", ", $parts);
							 */
						} else {
							if (App\Record::getType($referenceId) === $referenceModule) {
								$referenceRecordModel = Vtiger_Record_Model::getInstanceById($referenceId);
								$result = $referenceRecordModel->getDisplayValue($fieldname);
							} else {
								$result = '';
							}
						}
					}
				}
			}
		}
		return $result;
	}

	protected function useValue($fieldname, $moduleName)
	{
		return true;
	}

	function parseTemplate()
	{
		return preg_replace_callback('/\\$(\w+|\((\w+) : \(([_\w]+)\) (\w+)\))/', array($this, 'matchHandler'), $this->template);
	}

	function getCompanySetting($fieldname)
	{
		return Settings_Vtiger_CompanyDetails_Model::getSetting($fieldname);
	}

	function getMetaValue($fieldname)
	{
		require_once 'config/config.php';
		$siteURL = AppConfig::main('site_URL');
		$portalUrl = AppConfig::main('PORTAL_URL');

		switch ($fieldname) {
			case 'date' :
				$ownerId = $this->recordModel->get('assigned_user_id');
				if ($ownerId) {
					if (App\Fields\Owner::getType($ownerId) === 'Groups') {
						$ownerId = Vtiger_Util_Helper::getCreator($this->recordModel->getId());
					}
				}
				$ownerObject = new Users();
				$ownerObject->retrieveCurrentUserInfoFromFile($ownerId);

				$date = new DateTimeField(null);
				return $date->getDisplayDate($ownerObject);
			case 'time' :
				return Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat(date('h:i:s'));
			case 'dbtimezone' :
				return DateTimeField::getDBTimeZone();
			case 'usertimezone' :
				$ownerId = $this->recordModel->get('assigned_user_id');
				if ($ownerId) {
					if (App\Fields\Owner::getType($ownerId) === 'Groups') {
						$ownerId = Vtiger_Util_Helper::getCreator($this->recordModel->getId());
					}
				}
				if ($ownerId) {
					return \App\Language::translate(\App\User::getUserModel($ownerId)->getDetail('time_zone'), 'Users');
				}
				return '';
			case 'crmdetailviewurl' :
				$recordId = $this->recordModel->getId();
				$moduleName = $this->recordModel->getModuleName();
				return "<a href='$siteURL/index.php?module=$moduleName&view=Detail&record=$recordId'>" . \App\Language::translate($moduleName, $moduleName) . "</a>";
			case 'portaldetailviewurl' :
				$recordId = $this->recordModel->getId();
				$moduleName = $this->recordModel->getModuleName();
				$recorIdName = 'id';
				if ($moduleName === 'HelpDesk')
					$recorIdName = 'ticketid';
				if ($moduleName === 'Faq')
					$recorIdName = 'faqid';
				if ($moduleName === 'Products')
					$recorIdName = 'productid';
				return "<a href='" . $portalUrl . '/index.php?module=' . $moduleName . '&action=index&' . $recorIdName . '=' . $recordId . '&status=true' . "'>Portal</a>";
			case 'portalpdfurl' :
				$recordId = $this->recordModel->getId();
				$moduleName = $this->recordModel->getModuleName();
				$recorIdName = 'id';
				return "<a href='" . $portalUrl . '/index.php?module=' . $moduleName . '&action=index&' . $recorIdName . '=' . $recordId . '&downloadfile=true' . "'>Download</a>";
			case 'siteurl' :
				return "<a href='$siteURL'>$siteURL</a>";
			case 'portalurl' :
				return "<a href='$portalUrl'>$portalUrl</a>";
			case 'logo' :
				return '<img src="cid:logo" />';
			case 'recordId' :
				return $this->recordModel->getId();
			case 'supportName' :
				return AppConfig::main('HELPDESK_SUPPORT_NAME');
			case 'supportEmailId' :
				return AppConfig::main('HELPDESK_SUPPORT_EMAIL_REPLY');
			default: '';
		}
	}

	/**
	 * Function to fieldvalues of Comments
	 * @param <String> $moduleName
	 * @param <String> $fieldName
	 * @param <String> $fieldValue
	 * @return <String> $comments
	 */
	public function getComments($moduleName, $fieldName, $recordId)
	{
		$query = (new \App\Db\Query())->select(['commentcontent'])->from('vtiger_modcomments')->where(['related_to' => $recordId])->orderBy(['modcommentsid' => SORT_DESC]);
		switch ($fieldName) {
			case 'lastComment' :
				$query->limit(1);
				break;
			case 'last5Comments' :
				$query->limit(5);
				break;
		}
		$commentsList = '';
		foreach ($query->column() as $comment) {
			if ($comment != '') {
				$commentsList .= '<br><br>' . nl2br($comment);
			}
		}
		return $commentsList;
	}
}
