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

	function __construct($templateString)
	{
		$this->template = $templateString;
	}

	function render($entityCache, $entityId)
	{
		$this->cache = $entityCache;
		$this->parent = $this->cache->forId($entityId);
		return $this->parseTemplate();
	}

	private function matchHandler($match)
	{
		preg_match('/\((\w+) : \(([_\w]+)\) (\w+)\)/', $match[1], $matches);
		if ($match[1] == 'logo') {
			return $this->getMetaValue($match[1]);
		}
		// If parent is empty then we can't do any thing here
		if (!empty($this->parent)) {
			if (count($matches) == 0) {
				$fieldname = $match[1];

				//To handle comments for this module
				if (in_array($fieldname, array('lastComment', 'last5Comments', 'allComments'))) {
					return $this->getComments($this->parent->getModuleName(), $fieldname, $this->parent->getId());
				}

				$data = $this->parent->getData();

				if (isset($data[$fieldname]) || $fieldname === '_DATE_FORMAT_') {
					if ($this->useValue($data, $fieldname, $this->parent->getModuleName())) {
						$result = $this->transformToUserFormat($this->parent->getModuleName(), $fieldname, $data[$fieldname]);
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
				} else if ('__VtigerCompany__' == $referenceModule) {
					$result = $this->getCompanySetting($fieldname);
				} else {
					$referenceId = $this->parent->get($referenceField);
					if ($referenceId == null) {
						$result = "";
					} else {
						//To handle comments for this reference module
						if (in_array($fieldname, array('lastComment', 'last5Comments', 'allComments'))) {
							return $this->getComments($referenceModule, $fieldname, $referenceId);
						}

						if ($referenceField === 'contact_id') {
							$referenceIdsList = explode(',', $referenceId);
							$parts = array();
							foreach ($referenceIdsList as $referenceId) {
								$entity = $this->cache->forId($referenceId);
								$data = $entity->getData();
								if ($this->useValue($data, $fieldname, $referenceModule)) {
									$parts[] = $this->transformToUserFormat($referenceModule, $fieldname, $data[$fieldname]);
								}
							}
							return implode(',', $parts);
						}
						$entity = $this->cache->forId($referenceId);
						if ($referenceModule === "Users" && $entity->getModuleName() == "Groups") {
							list($groupEntityId, $groupId) = vtws_getIdComponents($referenceId);

							require_once('include/utils/GetGroupUsers.php');
							$ggu = new GetGroupUsers();
							$ggu->getAllUsersInGroup($groupId);

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
						} elseif ($entity->getModuleName() === $referenceModule) {
							$data = $entity->getData();
							if ($this->useValue($data, $fieldname, $referenceModule)) {
								$handler = vtws_getModuleHandlerFromName($referenceModule, $this->cache->user);
								$meta = $handler->getMeta();
								$referenceFieldList = $meta->getReferenceFieldDetails();
								if (array_key_exists($fieldname, $referenceFieldList)) {
									$webserviceId = $data[$fieldname];
									$idComponents = vtws_getIdComponents($webserviceId);
									if ($fieldname == 'currency_id' && $referenceModule == 'Users') {
										$result = decode_html(vtlib\Functions::getCurrencyName($idComponents[1]));
									} else {
										$result = decode_html(\includes\Record::getLabel($idComponents[1]));
									}
								} else {
									$result = $this->transformToUserFormat($referenceModule, $fieldname, $data[$fieldname]);
								}
							} else {
								$result = '';
							}
						} else {
							$result = '';
						}
					}
				}
			}
		}
		return $result;
	}

	protected function useValue($data, $fieldname, $moduleName)
	{
		return true;
	}

	function parseTemplate()
	{
		return preg_replace_callback('/\\$(\w+|\((\w+) : \(([_\w]+)\) (\w+)\))/', array($this, "matchHandler"), $this->template);
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
			case 'date' : $referenceId = $this->parent->get('assigned_user_id');
				if ($referenceId) {
					$entity = $this->cache->forId($referenceId);
					list($entityId, $ownerId) = vtws_getIdComponents($referenceId);
					if ($entity->getModuleName() === 'Groups') {
						list($moduleId, $recordId) = vtws_getIdComponents($this->parent->getId());
						$ownerId = Vtiger_Util_Helper::getCreator($recordId);
					}
				}
				$ownerObject = new Users();
				$ownerObject->retrieveCurrentUserInfoFromFile($ownerId);

				$date = new DateTimeField(null);
				return $date->getDisplayDate($ownerObject);
			case 'time' : return Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat(date('h:i:s'));
			case 'dbtimezone' : return DateTimeField::getDBTimeZone();
			case 'usertimezone' : $referenceId = $this->parent->get('assigned_user_id');
				if ($referenceId) {
					$entity = $this->cache->forId($referenceId);
					list($entityId, $ownerId) = vtws_getIdComponents($referenceId);
					if ($entity->getModuleName() === 'Groups') {
						list($moduleId, $recordId) = vtws_getIdComponents($this->parent->getId());
						$ownerId = Vtiger_Util_Helper::getCreator($recordId);
					}
				}

				if ($ownerId) {
					$ownerFocus = CRMEntity::getInstance('Users');
					$ownerFocus->retrieve_entity_info($ownerId, 'Users');
					return \includes\Language::translate($ownerFocus->column_fields['time_zone'], 'Users');
				}
				return '';
			case 'crmdetailviewurl' : $wsId = $this->parent->getId();
				$parts = explode('x', $wsId);
				$recordId = $parts[1];
				$moduleName = $this->parent->getModuleName();
				return "$siteURL/index.php?module=$moduleName&view=Detail&record=$recordId";
			case 'portaldetailviewurl' : $wsId = $this->parent->getId();
				$parts = explode('x', $wsId);
				$recordId = $parts[1];
				$moduleName = $this->parent->getModuleName();
				$recorIdName = 'id';
				if ($moduleName == 'HelpDesk')
					$recorIdName = 'ticketid';
				if ($moduleName == 'Faq')
					$recorIdName = 'faqid';
				if ($moduleName == 'Products')
					$recorIdName = 'productid';
				return $portalUrl . '/index.php?module=' . $moduleName . '&action=index&' . $recorIdName . '=' . $recordId . '&status=true';
			case 'portalpdfurl' : $wsId = $this->parent->getId();
				$parts = explode('x', $wsId);
				$recordId = $parts[1];
				$moduleName = $this->parent->getModuleName();
				$recorIdName = 'id';
				return $portalUrl . '/index.php?module=' . $moduleName . '&action=index&' . $recorIdName . '=' . $recordId . '&downloadfile=true';
			case 'siteurl' : return $siteURL;
			case 'portalurl' : return $portalUrl;
			case 'logo' : return '<img src="cid:logo" />';

			case 'recordId' : list($moduleId, $recordId) = vtws_getIdComponents($this->parent->getId());
				return $recordId;

			case 'supportName' : return AppConfig::main('HELPDESK_SUPPORT_NAME');
			case 'supportEmailId' : return AppConfig::main('HELPDESK_SUPPORT_EMAIL_REPLY');
			default: '';
		}
	}

	/**
	 * Function to transform the field values into user format
	 * @param <String> $moduleName
	 * @param <String> $fieldName
	 * @param <String> $fieldValue
	 * @return <String> $fieldValue
	 */
	public function transformToUserFormat($moduleName, $fieldName, $fieldValue)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$log = vglobal('log');

		//getting owner info
		$referenceId = $this->parent->get('assigned_user_id');
		if ($referenceId) {
			$entity = $this->cache->forId($referenceId);
			list($entityId, $ownerId) = vtws_getIdComponents($referenceId);

			if ($entity->getModuleName() === 'Groups') {
				list($moduleId, $recordId) = vtws_getIdComponents($this->parent->getId());
				$ownerId = Vtiger_Util_Helper::getCreator($recordId);
			}
		}
		if (!empty($ownerId)) {
			$ownerObject = new Users();
			$ownerObject->retrieveCurrentUserInfoFromFile($ownerId);
		}

		if ($ownerObject && $fieldName === '_DATE_FORMAT_') {
			return $ownerObject->column_fields['date_format'];
		}

		//getting field instance info
		$entityObject = VtigerWebserviceObject::fromName($adb, $moduleName);
		$handlerPath = $entityObject->getHandlerPath();
		$handlerClass = $entityObject->getHandlerClass();

		require_once $handlerPath;

		$entityHandler = new $handlerClass($entityObject, $current_user, $adb, $log);
		$entityMeta = $entityHandler->getMeta();
		$entityFields = $entityMeta->getModuleFields();
		$fieldInstance = $entityFields[$fieldName];
		switch ($fieldInstance->getFieldDataType()) {
			case 'date' : if (in_array($moduleName, ['Events', 'Calendar']) && in_array($fieldName, ['date_start', 'due_date'])) {
					if ($fieldName === 'date_start') {
						$dateTime = $this->parent->get('date_start') . ' ' . $this->parent->get('time_start');
					} else {
						$dateTime = $this->parent->get('due_date') . ' ' . $this->parent->get('time_end');
					}
					$fieldValue = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($dateTime, $ownerObject);
				} else {
					$dateFieldObj = new DateTimeField($fieldValue);
					$fieldValue = $dateFieldObj->getDisplayDate($ownerObject);
				}
				break;
			case 'datetime' : if (in_array($moduleName, ['Events', 'Calendar']) && in_array($fieldName, ['date_start', 'due_date'])) {
					if ($fieldName === 'date_start') {
						$fieldValue = $this->parent->get('date_start') . ' ' . $this->parent->get('time_start');
					} else {
						$fieldValue = $this->parent->get('due_date') . ' ' . $this->parent->get('time_end');
					}
				}
				$fieldValue = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat($fieldValue, $ownerObject);
				break;

			case 'currency' : if ($fieldInstance->getUIType() === '72') {
					$fieldValue = CurrencyField::convertToUserFormat($fieldValue, $ownerObject, true);
				} else if ($fieldInstance->getUIType() == '71') {
					$fieldValue = CurrencyField::convertToUserFormat($fieldValue, $ownerObject);
				}
				break;

			case 'time' : $fieldValue = Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat($fieldValue, $ownerObject);
				break;

			case 'picklist' : require_once 'include/runtime/LanguageHandler.php';
				require_once 'include/runtime/Globals.php';
				$fieldValue = vtranslate($fieldValue, $moduleName, $ownerObject->column_fields['language']);
				break;

			case 'multipicklist' : require_once 'include/runtime/LanguageHandler.php';
				require_once 'include/runtime/Globals.php';
				$fieldValueParts = explode(';', $fieldValue);
				foreach ($fieldValueParts as $index => $fieldValue) {
					$fieldValueParts[$index] = vtranslate($fieldValue, $moduleName, $ownerObject->column_fields['language']);
				}
				$fieldValue = implode(';', $fieldValueParts);
				break;
			case 'boolean' : require_once 'include/runtime/LanguageHandler.php';
				require_once 'include/runtime/Globals.php';
				if ($fieldValue == 1) {
					$fieldValue = vtranslate('LBL_YES', $moduleName);
				} else {
					$fieldValue = vtranslate('LBL_NO', $moduleName);
				}
				break;
			case 'reference' : if ($fieldName == 'currency_id') {
					$currencyId = explode('x', $fieldValue);
					$currencyId = $currencyId[1];
					$fieldValue = decode_html(vtlib\Functions::getCurrencyName($currencyId));
				} else {
					if ($fieldValue != 0 && $fieldValue != null) {
						$id = explode('x', $fieldValue);
						$webServiceObject = VtigerWebserviceObject::fromId($adb, $id[0]);
						if ($webServiceObject) {
							$Value = getEntityName($webServiceObject->getEntityName(), $id);
							$fieldValue = $Value[$id[1]];
						}
					}
				}
				break;
			case 'double' : if ($moduleName === 'HelpDesk' && in_array($fieldName, array('days', 'hours'))) {
					$fieldValue = \vtlib\Functions::formatDecimal($fieldValue);
				} else {
					$fieldValue;
				}
				break;
			case 'owner' : $id = explode('x', $fieldValue);
				$webServiceObject = VtigerWebserviceObject::fromId($adb, $id[0]);
				if ($webServiceObject) {
					$Value = getEntityName($webServiceObject->getEntityName(), $id);
					$fieldValue = $Value[$id[1]];
				}
				break;
			default : $fieldValue;
		}
		return nl2br($fieldValue);
	}

	/**
	 * Function to fieldvalues of Comments
	 * @param <String> $moduleName
	 * @param <String> $fieldName
	 * @param <String> $fieldValue
	 * @return <String> $comments
	 */
	public function getComments($moduleName, $fieldName, $fieldValue)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$log = vglobal('log');

		$sql = 'SELECT commentcontent FROM vtiger_modcomments WHERE related_to = ? ORDER BY modcommentsid DESC';

		switch ($fieldName) {
			case 'lastComment' : $sql .= ' LIMIT 1';
				break;
			case 'last5Comments' : $sql .= ' LIMIT 5';
				break;
			default : $sql;
				break;
		}
		list($entityId, $recordId) = vtws_getIdComponents($fieldValue);

		$result = $adb->pquery($sql, array($recordId));
		$numOfRows = $adb->num_rows($result);

		$commentsList = '';
		for ($i = 0; $i < $numOfRows; $i++) {
			$comment = $adb->query_result($result, $i, 'commentcontent');
			if ($comment != '') {
				$commentsList .= '<br><br>' . nl2br($comment);
			}
		}

		return $commentsList;
	}
}
