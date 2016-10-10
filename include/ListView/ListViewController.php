<?php
/* +*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ******************************************************************************* */

/**
 * Description of ListViewController
 *
 * @author MAK
 */
class ListViewController
{

	/**
	 *
	 * @var QueryGenerator
	 */
	private $queryGenerator;

	/**
	 *
	 * @var PearDatabase
	 */
	private $db;
	private $nameList;
	private $typeList;
	private $ownerNameList;
	private $user;
	private $picklistValueMap;
	private $picklistRoleMap;
	private $headerSortingEnabled;
	public $rawData;

	public function __construct($db, $user, $generator)
	{
		$this->queryGenerator = $generator;
		$this->db = $db;
		$this->user = $user;
		$this->nameList = [];
		$this->typeList = [];
		$this->ownerNameList = [];
		$this->picklistValueMap = [];
		$this->picklistRoleMap = [];
		$this->headerSortingEnabled = true;
		$this->rawData = [];
	}

	public function isHeaderSortingEnabled()
	{
		return $this->headerSortingEnabled;
	}

	public function setHeaderSorting($enabled)
	{
		$this->headerSortingEnabled = $enabled;
	}

	public function setupAccessiblePicklistValueList($name)
	{
		$isRoleBased = vtws_isRoleBasedPicklist($name);
		$this->picklistRoleMap[$name] = $isRoleBased;
		if ($this->picklistRoleMap[$name]) {
			$this->picklistValueMap[$name] = getAssignedPicklistValues($name, $this->user->roleid, $this->db);
		}
	}

	public function fetchNameList($field, $result)
	{
		$referenceFieldInfoList = $this->queryGenerator->getReferenceFieldInfoList();
		$fieldName = $field->getFieldName();
		$rowCount = $this->db->num_rows($result);

		$idList = [];
		for ($i = 0; $i < $rowCount; $i++) {
			$id = $this->db->query_result($result, $i, $field->getColumnName());
			if (!isset($this->nameList[$fieldName][$id])) {
				$idList[$id] = $id;
			}
		}

		$idList = array_keys($idList);
		if (count($idList) == 0) {
			return;
		}
		$moduleList = $referenceFieldInfoList[$fieldName];
		foreach ($moduleList as $module) {
			$meta = $this->queryGenerator->getMeta($module);
			if ($meta->isModuleEntity()) {
				if ($module == 'Users') {
					$nameList = \includes\fields\Owner::getLabel($idList);
				} else {
					$nameList = getEntityName($module, $idList);
				}
			} else {
				$nameList = vtws_getActorEntityName($module, $idList);
			}
			$entityTypeList = array_intersect(array_keys($nameList), $idList);
			foreach ($entityTypeList as $id) {
				$this->typeList[$id] = $module;
			}
			if (empty($this->nameList[$fieldName])) {
				$this->nameList[$fieldName] = [];
			}
			foreach ($entityTypeList as $id) {
				$this->typeList[$id] = $module;
				$this->nameList[$fieldName][$id] = $nameList[$id];
			}
		}
	}

	public function getListViewHeaderFields()
	{
		$meta = $this->queryGenerator->getMeta($this->queryGenerator->getModule());
		$moduleFields = $this->queryGenerator->getModuleFields();
		$fields = $this->queryGenerator->getFields();
		$headerFields = [];
		foreach ($fields as $fieldName) {
			if (array_key_exists($fieldName, $moduleFields)) {
				$headerFields[$fieldName] = $moduleFields[$fieldName];
			}
		}
		return $headerFields;
	}

	public function getListViewRecords($focus, $module, $result)
	{
		$listview_max_textlength = vglobal('listview_max_textlength');
		$default_charset = vglobal('default_charset');

		require('user_privileges/user_privileges_' . $this->user->id . '.php');
		$fields = $this->queryGenerator->getFields();
		$meta = $this->queryGenerator->getMeta($this->queryGenerator->getModule());

		$moduleFields = $this->queryGenerator->getModuleFields();
		$accessibleFieldList = array_keys($moduleFields);
		$listViewFields = array_intersect($fields, $accessibleFieldList);

		$referenceFieldList = $this->queryGenerator->getReferenceFieldList();
		foreach ($referenceFieldList as $fieldName) {
			if (in_array($fieldName, $listViewFields)) {
				$field = $moduleFields[$fieldName];
				$this->fetchNameList($field, $result);
			}
		}

		$db = PearDatabase::getInstance();
		$rowCount = $db->num_rows($result);
		$ownerFieldList = $this->queryGenerator->getOwnerFieldList();
		foreach ($ownerFieldList as $fieldName) {
			if (in_array($fieldName, $listViewFields)) {
				$field = $moduleFields[$fieldName];
				$idList = [];
				for ($i = 0; $i < $rowCount; $i++) {
					$id = $this->db->query_result($result, $i, $field->getColumnName());
					if (!isset($this->ownerNameList[$fieldName][$id])) {
						$idList[] = $id;
					}
				}
				if (count($idList) > 0) {
					if (isset($this->ownerNameList[$fieldName]) && !is_array($this->ownerNameList[$fieldName])) {
						$this->ownerNameList[$fieldName] = \includes\fields\Owner::getLabel($idList);
					} else {
						//array_merge API loses key information so need to merge the arrays
						// manually.
						$newOwnerList = \includes\fields\Owner::getLabel($idList);
						foreach ($newOwnerList as $id => $name) {
							$this->ownerNameList[$fieldName][$id] = $name;
						}
					}
				}
			}
		}

		foreach ($listViewFields as $fieldName) {
			$field = $moduleFields[$fieldName];
			if (!$is_admin && ($field->getFieldDataType() == 'picklist' ||
				$field->getFieldDataType() == 'multipicklist')) {
				$this->setupAccessiblePicklistValueList($fieldName);
			}
		}
		$useAsterisk = get_use_asterisk($this->user->id);

		$data = $rawData = [];
		for ($i = 0; $i < $rowCount; ++$i) {
			//Getting the recordId
			if ($module != 'Users') {
				$baseTable = $meta->getEntityBaseTable();
				$moduleTableIndexList = $meta->getEntityTableIndexList();
				$baseTableIndex = $moduleTableIndexList[$baseTable];

				$recordId = $db->query_result($result, $i, $baseTableIndex);
			} else {
				$recordId = $db->query_result($result, $i, "id");
			}
			$row = [];
			$rawData[$recordId] = ['id' => $recordId];
			foreach ($listViewFields as $fieldName) {
				$field = $moduleFields[$fieldName];
				$uitype = $field->getUIType();
				$rawValue = $this->db->query_result($result, $i, $field->getColumnName());
				$rawData[$recordId][$fieldName] = $rawValue;
				$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($field->getFieldId());
				if (in_array($uitype, array(15, 33, 16))) {
					$value = html_entity_decode($rawValue, ENT_QUOTES, $default_charset);
				} else {
					$value = $rawValue;
				}
				if ($uitype == 308) {
					$value = $fieldModel->getUITypeModel()->getListViewDisplayValue($value);
				} elseif ($module == 'Documents' && $fieldName == 'filename') {
					$downloadtype = $db->query_result($result, $i, 'filelocationtype');
					$fileName = $db->query_result($result, $i, 'filename');

					$downloadType = $db->query_result($result, $i, 'filelocationtype');
					$status = $db->query_result($result, $i, 'filestatus');
					$fileIdQuery = 'select attachmentsid from vtiger_seattachmentsrel where crmid=?';
					$fileIdRes = $db->pquery($fileIdQuery, [$recordId]);
					$fileId = $db->getSingleValue($fileIdRes);
					if ($fileName != '' && $status == 1) {
						if ($downloadType == 'I') {
							$value = '<a onclick="Javascript:Documents_Index_Js.updateDownloadCount(\'index.php?module=Documents&action=UpdateDownloadCount&record=' . $recordId . '\');"' .
								' href="index.php?module=Documents&action=DownloadFile&record=' . $recordId . '&fileid=' . $fileId . '"' .
								' title="' . \includes\Language::translate('LBL_DOWNLOAD_FILE', $module) .
								'" >' . vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext')) .
								'</a>';
						} elseif ($downloadType == 'E') {
							$value = '<a onclick="Javascript:Documents_Index_Js.updateDownloadCount(\'index.php?module=Documents&action=UpdateDownloadCount&record=' . $recordId . '\');"' .
								' href="' . $fileName . '" target="_blank"' .
								' title="' . \includes\Language::translate('LBL_DOWNLOAD_FILE', $module) .
								'" >' . vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext')) .
								'</a>';
						} else {
							$value = ' --';
						}
					}
					$value = $fileicon . $value;
				} elseif ($module == 'Documents' && $fieldName == 'filesize') {
					$downloadType = $db->query_result($result, $i, 'filelocationtype');
					if ($downloadType == 'I') {
						$filesize = $value;
						if ($filesize < 1024)
							$value = $filesize . ' B';
						elseif ($filesize > 1024 && $filesize < 1048576)
							$value = round($filesize / 1024, 2) . ' KB';
						else if ($filesize > 1048576)
							$value = round($filesize / (1024 * 1024), 2) . ' MB';
					} else {
						$value = ' --';
					}
				} elseif ($module == 'Documents' && $fieldName == 'filestatus') {
					if ($value == 1)
						$value = \includes\Language::translate('yes', $module);
					elseif ($value == 0)
						$value = \includes\Language::translate('no', $module);
					else
						$value = '--';
				} elseif ($module == 'Documents' && $fieldName == 'filetype') {
					$downloadType = $db->query_result($result, $i, 'filelocationtype');
					if ($downloadType == 'E' || $downloadType != 'I') {
						$value = '--';
					}
				} elseif ($module == 'OSSTimeControl' && $fieldName == 'sum_time') {
					$value = vtlib\Functions::decimalTimeFormat($value);
					$value = $value['short'];
				} elseif ($field->getUIType() == '27') {
					if ($value == 'I') {
						$value = \includes\Language::translate('LBL_INTERNAL', $module);
					} elseif ($value == 'E') {
						$value = \includes\Language::translate('LBL_EXTERNAL', $module);
					} else {
						$value = ' --';
					}
					$value = vtlib\Functions::textLength($value);
				} elseif ($field->getFieldDataType() == 'picklist') {
					$value = \includes\Language::translate($value, $module);
					$value = vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext'));
				} elseif ($field->getFieldDataType() == 'date' || $field->getFieldDataType() == 'datetime') {
					if ($value != '' && $value != '0000-00-00') {
						$fieldDataType = $field->getFieldDataType();
						if ($module == 'Calendar' && ($fieldName == 'date_start' || $fieldName == 'due_date')) {
							if ($fieldName == 'date_start') {
								$timeField = 'time_start';
							} else if ($fieldName == 'due_date') {
								$timeField = 'time_end';
							}
							$timeFieldValue = $this->db->query_result($result, $i, $timeField);
							if (!empty($timeFieldValue)) {
								$value .= ' ' . $timeFieldValue;
								//TO make sure it takes time value as well
								$fieldDataType = 'datetime';
							}
						}
						if ($fieldDataType == 'datetime') {
							$value = Vtiger_Datetime_UIType::getDateTimeValue($value);
						} else if ($fieldDataType == 'date') {
							$date = new DateTimeField($value);
							$value = $date->getDisplayDate();
						}
					} elseif ($value == '0000-00-00') {
						$value = '';
					}
				} elseif ($field->getFieldDataType() == 'time') {
					if (!empty($value)) {
						$userModel = Users_Privileges_Model::getCurrentUserModel();
						if ($userModel->get('hour_format') == '12') {
							$value = Vtiger_Time_UIType::getTimeValueInAMorPM($value);
						}
					}
				} elseif ($field->getFieldDataType() == 'currency') {
					if ($value != '') {
						if ($field->getUIType() == 72) {
							if ($fieldName == 'unit_price') {
								$currencyId = getProductBaseCurrency($recordId, $module);
								$cursym_convrate = \vtlib\Functions::getCurrencySymbolandRate($currencyId);
								$currencySymbol = $cursym_convrate['symbol'];
							} else {
								$currencyInfo = getInventoryCurrencyInfo($module, $recordId);
								$currencySymbol = $currencyInfo['currency_symbol'];
							}
							$value = CurrencyField::convertToUserFormat($value, null, true);
							$row['currencySymbol'] = $currencySymbol;
							$value = CurrencyField::appendCurrencySymbol($value, $currencySymbol);
						} else {
							if (!empty($value)) {
								$value = CurrencyField::convertToUserFormat($value);
								$currencyModal = new CurrencyField($value);
								$currencyModal->initialize();
								$value = $currencyModal->appendCurrencySymbol($value, $currencyModal->currencySymbol);
							}
						}
					}
				} elseif ($field->getFieldDataType() == 'url') {
					$matchPattern = "^[\w]+:\/\/^";
					preg_match($matchPattern, $rawValue, $matches);
					if (!empty($matches[0])) {
						$value = '<a class="urlField cursorPointer" title="' . $rawValue . '" href="' . $rawValue . '" target="_blank">' . vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext')) . '</a>';
					} else {
						$value = '<a class="urlField cursorPointer" title="' . $rawValue . '" href="http://' . $rawValue . '" target="_blank">' . vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext')) . '</a>';
					}
				} elseif ($field->getFieldDataType() == 'email') {
					$currentUser = Users_Record_Model::getCurrentUserModel();
					$value = vtlib\Functions::textLength($value);
					if ($currentUser->get('internal_mailer') == 1) {
						$url = OSSMail_Module_Model::getComposeUrl($module, $recordId, 'Detail', 'new');
						$mailConfig = OSSMail_Module_Model::getComposeParameters();
						$value = "<a class=\"cursorPointer sendMailBtn\" data-url=\"$url\" data-module=\"$module\" data-record=\"$recordId\" data-to=\"$rawValue\" data-popup=" . $mailConfig['popup'] . " title=" . vtranslate('LBL_SEND_EMAIL') . ">$value</a>";
					} else {
						$value = '<a class="emailField" href="mailto:' . $rawValue . '">' . $value . '</a>';
					}
				} elseif ($field->getFieldDataType() == 'boolean') {
					if ($value === 'on') {
						$value = 1;
					} else if ($value == 'off') {
						$value = 0;
					}
					if ($value == 1) {
						$value = \includes\Language::translate('yes', $module);
					} elseif ($value == 0) {
						$value = \includes\Language::translate('no', $module);
					} else {
						$value = '--';
					}
				} elseif ($field->getUIType() == 98) {
					$value = '<a href="index.php?module=Roles&parent=Settings&view=Edit&record=' . $value . '">' . vtlib\Functions::textLength(getRoleName($value), $fieldModel->get('maxlengthtext')) . '</a>';
				} elseif ($field->getFieldDataType() == 'multipicklist') {
					$valueArray = ($value != "") ? explode(' |##| ', $value) : [];
					foreach ($valueArray as $key => $valueSingle) {
						$valueArray[$key] = \includes\Language::translate($valueSingle, $module);
					}
					$value = implode(', ', $valueArray);
					$value = vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext'));
				} elseif ($field->getFieldDataType() == 'skype') {
					if (empty($value)) {
						$value = '';
					} else {
						$value = "<a href='skype:$value?call'>" . vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext')) . '</a>';
					}
				} elseif ($field->getUIType() == 11) {
					$moduleInstance = Vtiger_Module_Model::getInstance("PBXManager");
					if ($moduleInstance && $moduleInstance->isActive()) {
						$outgoingCallPermission = PBXManager_Server_Model::checkPermissionForOutgoingCall();
					}
					$outgoingMobilePermission = Vtiger_Mobile_Model::checkPermissionForOutgoingCall();
					if ($outgoingCallPermission && !empty($value)) {
						$phoneNumber = preg_replace('/[-()\s+]/', '', $value);
						$value = vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext'));
						$value = $value . '<a class="phoneField" data-value="' . $phoneNumber . '" record="' . $recordId . '"onclick="Vtiger_PBXManager_Js.registerPBXOutboundCall(\'' . $phoneNumber . '\', ' . $recordId . ')"> <img style="vertical-align:middle;" src="layouts/basic/skins/images/small_Call.png"/></a>';
					} elseif ($outgoingMobilePermission && !empty($value)) {
						$phoneNumber = preg_replace('/[-()\s]/', '', $value);
						$value = '<a class="phoneField" data-phoneNumber="' . $phoneNumber . '" record="' . $recordId . '" onclick="Vtiger_Mobile_Js.registerOutboundCall(\'' . $phoneNumber . '\', ' . $recordId . ')">' . textlength_check($value) . '</a>';
						$callUsers = Vtiger_Mobile_Model::getPrivilegesUsers();
						if ($callUsers) {
							$value .= '  <a class="btn btn-xs noLinkBtn" onclick="Vtiger_Mobile_Js.registerOutboundCallToUser(this,\'' . $phoneNumber . '\',' . $recordId . ')" data-placement="right" data-original-title="' . vtranslate('LBL_SELECT_USER_TO_CALL', $module) . '" data-content=\'<select class="select sesectedUser" name="sesectedUser">';
							foreach ($callUsers as $key => $item) {
								$value .= '<option value="' . $key . '">' . $item . '</option>';
							}
							$value .= '</select><br /><a class="btn btn-success popoverCallOK">' . vtranslate('LBL_BTN_CALL', $module) . '</a>   <a class="btn btn-inverse popoverCallCancel">' . vtranslate('LBL_CANCEL', $module) . '</a>\' data-trigger="manual"><i class="icon-user"></i></a>';
						}
					} else {
						$value = vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext'));
					}
				} elseif (in_array($field->getFieldDataType(), Vtiger_Field_Model::$REFERENCE_TYPES)) {
					$referenceFieldInfoList = $this->queryGenerator->getReferenceFieldInfoList();
					$moduleList = $referenceFieldInfoList[$fieldName];
					if (count($moduleList) == 1) {
						$parentModule = reset($moduleList);
					} else {
						$parentModule = $this->typeList[$value];
					}
					if (!empty($value) && !empty($this->nameList[$fieldName]) && !empty($parentModule)) {
						$parentMeta = $this->queryGenerator->getMeta($parentModule);
						$ID = $value;
						if ($parentMeta->isModuleEntity() && $parentModule != 'Users' && Users_Privileges_Model::isPermitted($parentModule, 'DetailView', $ID)) {
							$className = $fieldModel->getModule()->getName() . '_' . ucwords($fieldModel->get('name')) . '_Field';
							if (class_exists($className)) {
								$customField = new $className();
								$value = $customField->getListViewDisplayValue($rawValue, $fieldModel);
							} else {
								$value = vtlib\Functions::textLength($this->nameList[$fieldName][$ID], $fieldModel->get('maxlengthtext'));
								$value = "<a class='moduleColor_$parentModule' href='?module=$parentModule&view=Detail&" .
									"record=$rawValue' title='" . \includes\Language::translate($parentModule, $parentModule) . "'>$value</a>";
							}
						} else {
							$value = vtlib\Functions::textLength($this->nameList[$fieldName][$ID], $fieldModel->get('maxlengthtext'));
						}
					} else {
						$value = '--';
					}
				} elseif ($field->getFieldDataType() == 'owner') {
					$value = vtlib\Functions::textLength($this->ownerNameList[$fieldName][$value], $fieldModel->get('maxlengthtext'));
				} elseif ($field->getUIType() == 8) {
					if (!empty($value)) {
						$temp_val = html_entity_decode($value, ENT_QUOTES, $default_charset);
						$value = vtlib\Functions::suppressHTMLTags(implode(',', \includes\utils\Json::decode($temp_val)));
					}
				} elseif ($field->getFieldDataType() == 'taxes') {
					if (!empty($value)) {
						$valueArray = ($value != '') ? explode(',', $value) : [];
						$tmp = '';
						$tmpArray = [];
						$taxs = Vtiger_Taxes_UIType::getTaxes();
						foreach ($valueArray as $index => $tax) {
							if (isset($taxs[$tax])) {
								$tmpArray[] = $taxs[$tax]['value'] . '% - ' . $taxs[$tax]['name'];
							}
						}
						$value = implode(', ', $tmpArray);
						$value = vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext'));
					}
				} elseif ($field->getFieldDataType() == 'inventoryLimit') {
					if (!empty($value)) {
						$valueArray = ($value != "") ? explode(',', $value) : [];
						$tmp = '';
						$tmpArray = [];
						$limits = Vtiger_InventoryLimit_UIType::getLimits();
						foreach ($valueArray as $index => $limit) {
							if (isset($limits[$limit])) {
								$tmpArray[] = $limits[$limit]['value'] . ' - ' . $limits[$limit]['name'];
							}
						}
						$value = implode(', ', $tmpArray);
						$value = vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext'));
					}
				} elseif ($field->getFieldDataType() == 'multiReferenceValue') {
					$valueTmp = trim($value, '|#|');
					$valueTmp = ($valueTmp != "") ? explode('|#|', $valueTmp) : [];
					foreach ($valueTmp as $index => $tmp) {
						$valueTmp[$index] = $fieldModel->getUITypeModel()->getDisplayValue($tmp);
					}
					$value = implode(', ', $valueTmp);
					$value = vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext'));
				} elseif ($field->getFieldDataType() == 'posList') {
					$value = vtlib\Functions::textLength($fieldModel->getUITypeModel()->getDisplayValue($value), $fieldModel->get('maxlengthtext'));
				} elseif (in_array($uitype, array(7, 9, 90))) {
					$value = vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext'));
				} elseif ($uitype == 307) {
					if ($value === null) {
						$value = '--';
					} else {
						$value = "<span align='right'>" . vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext')) . '</span>';
					}
				} else {
					$value = vtlib\Functions::textLength($value, $fieldModel->get('maxlengthtext'));
				}
				$row[$fieldName] = $value;
			}
			$data[$recordId] = $row;
		}
		$this->rawData = $rawData;
		return $data;
	}
}
