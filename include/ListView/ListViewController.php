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

	public function __construct($db, $user, $generator)
	{
		$this->queryGenerator = $generator;
		$this->db = $db;
		$this->user = $user;
		$this->nameList = array();
		$this->typeList = array();
		$this->ownerNameList = array();
		$this->picklistValueMap = array();
		$this->picklistRoleMap = array();
		$this->headerSortingEnabled = true;
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

		$idList = array();
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
					$nameList = getOwnerNameList($idList);
				} else {
					//TODO handle multiple module names overriding each other.
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
				$this->nameList[$fieldName] = array();
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
		$headerFields = array();
		foreach ($fields as $fieldName) {
			if (array_key_exists($fieldName, $moduleFields)) {
				$headerFields[$fieldName] = $moduleFields[$fieldName];
			}
		}
		return $headerFields;
	}

	function getListViewRecords($focus, $module, $result)
	{
		global $listview_max_textlength, $theme, $default_charset;

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
				$idList = array();
				for ($i = 0; $i < $rowCount; $i++) {
					$id = $this->db->query_result($result, $i, $field->getColumnName());
					if (!isset($this->ownerNameList[$fieldName][$id])) {
						$idList[] = $id;
					}
				}
				if (count($idList) > 0) {
					if (!is_array($this->ownerNameList[$fieldName])) {
						$this->ownerNameList[$fieldName] = getOwnerNameList($idList);
					} else {
						//array_merge API loses key information so need to merge the arrays
						// manually.
						$newOwnerList = getOwnerNameList($idList);
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

		$data = array();
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
			$row = array();

			foreach ($listViewFields as $fieldName) {
				$field = $moduleFields[$fieldName];
				$uitype = $field->getUIType();
				$rawValue = $this->db->query_result($result, $i, $field->getColumnName());

				if (in_array($uitype, array(15, 33, 16))) {
					$value = html_entity_decode($rawValue, ENT_QUOTES, $default_charset);
				} else {
					$value = $rawValue;
				}

				if ($module == 'Documents' && $fieldName == 'filename') {
					$downloadtype = $db->query_result($result, $i, 'filelocationtype');
					$fileName = $db->query_result($result, $i, 'filename');

					$downloadType = $db->query_result($result, $i, 'filelocationtype');
					$status = $db->query_result($result, $i, 'filestatus');
					$fileIdQuery = "select attachmentsid from vtiger_seattachmentsrel where crmid=?";
					$fileIdRes = $db->pquery($fileIdQuery, array($recordId));
					$fileId = $db->query_result($fileIdRes, 0, 'attachmentsid');
					if ($fileName != '' && $status == 1) {
						if ($downloadType == 'I') {
							$value = '<a onclick="Javascript:Documents_Index_Js.updateDownloadCount(\'index.php?module=Documents&action=UpdateDownloadCount&record=' . $recordId . '\');"' .
								' href="index.php?module=Documents&action=DownloadFile&record=' . $recordId . '&fileid=' . $fileId . '"' .
								' title="' . getTranslatedString('LBL_DOWNLOAD_FILE', $module) .
								'" >' . textlength_check($value) .
								'</a>';
						} elseif ($downloadType == 'E') {
							$value = '<a onclick="Javascript:Documents_Index_Js.updateDownloadCount(\'index.php?module=Documents&action=UpdateDownloadCount&record=' . $recordId . '\');"' .
								' href="' . $fileName . '" target="_blank"' .
								' title="' . getTranslatedString('LBL_DOWNLOAD_FILE', $module) .
								'" >' . textlength_check($value) .
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
						$value = getTranslatedString('yes', $module);
					elseif ($value == 0)
						$value = getTranslatedString('no', $module);
					else
						$value = '--';
				} elseif ($module == 'Documents' && $fieldName == 'filetype') {
					$downloadType = $db->query_result($result, $i, 'filelocationtype');
					if ($downloadType == 'E' || $downloadType != 'I') {
						$value = '--';
					}
				} elseif ($module == 'OSSTimeControl' && $fieldName == 'sum_time') {
					$value = Vtiger_Functions::decimalTimeFormat($value);
					$value = $value['short'];
				} elseif ($field->getUIType() == '27') {
					if ($value == 'I') {
						$value = getTranslatedString('LBL_INTERNAL', $module);
					} elseif ($value == 'E') {
						$value = getTranslatedString('LBL_EXTERNAL', $module);
					} else {
						$value = ' --';
					}
					$value = Vtiger_Functions::textLength($value);
				} elseif ($field->getFieldDataType() == 'picklist') {
					$value = Vtiger_Language_Handler::getTranslatedString($value, $module);
					$value = textlength_check($value);
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
								$cursym_convrate = getCurrencySymbolandCRate($currencyId);
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
						$value = '<a class="urlField cursorPointer" title="' . $rawValue . '" href="' . $rawValue . '" target="_blank">' . textlength_check($value) . '</a>';
					} else {
						$value = '<a class="urlField cursorPointer" title="' . $rawValue . '" href="http://' . $rawValue . '" target="_blank">' . textlength_check($value) . '</a>';
					}
				} elseif ($field->getFieldDataType() == 'email') {
					$current_user = vglobal('current_user');
					if ($current_user->internal_mailer == 1) {
						//check added for email link in user detailview
						$value = "<a class='emailField' onclick=\"Vtiger_Helper_Js.getInternalMailer($recordId," .
							"'$fieldName','$module');\">" . textlength_check($value) . "</a>";
					} else {
						$value = '<a class="emailField" href="mailto:' . $rawValue . '">' . textlength_check($value) . '</a>';
					}
				} elseif ($field->getFieldDataType() == 'boolean') {
					if ($value === 'on') {
						$value = 1;
					} else if ($value == 'off') {
						$value = 0;
					}
					if ($value == 1) {
						$value = getTranslatedString('yes', $module);
					} elseif ($value == 0) {
						$value = getTranslatedString('no', $module);
					} else {
						$value = '--';
					}
				} elseif ($field->getUIType() == 98) {
					$value = '<a href="index.php?module=Roles&parent=Settings&view=Edit&record=' . $value . '">' . textlength_check(getRoleName($value)) . '</a>';
				} elseif ($field->getFieldDataType() == 'multipicklist') {
					$value = ($value != "") ? str_replace(' |##| ', ', ', $value) : "";
					if (!$is_admin && $value != '') {
						$valueArray = ($rawValue != "") ? explode(' |##| ', $rawValue) : array();
						$tmp = '';
						$tmpArray = array();
						foreach ($valueArray as $index => $val) {
							if (!$listview_max_textlength || !(strlen(preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $tmp)) > $listview_max_textlength)) {
								$tmpArray[] = $val;
								$tmp .= ', ' . $val;
							} else {
								$tmpArray[] = '...';
								$tmp .= '...';
							}
						}
						$value = implode(', ', $tmpArray);
						$value = textlength_check($value);
					}
				} elseif ($field->getFieldDataType() == 'skype') {
					$value = ($value != "") ? "<a href='skype:$value?call'>" . textlength_check($value) . "</a>" : "";
				} elseif ($field->getUIType() == 11) {
					$outgoingCallPermission = Vtiger_Mobile_Model::checkPermissionForOutgoingCall();
					if ($outgoingCallPermission && !empty($value)) {
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
						$value = textlength_check($value);
					}
				} elseif ($field->getFieldDataType() == 'reference') {
					$referenceFieldInfoList = $this->queryGenerator->getReferenceFieldInfoList();
					$moduleList = $referenceFieldInfoList[$fieldName];
					if (count($moduleList) == 1) {
						$parentModule = reset($moduleList);
					} else {
						$parentModule = $this->typeList[$value];
					}
					if (!empty($value) && !empty($this->nameList[$fieldName]) && !empty($parentModule)) {
						$parentMeta = $this->queryGenerator->getMeta($parentModule);
						$value = textlength_check($this->nameList[$fieldName][$value]);
						if ($parentMeta->isModuleEntity() && $parentModule != "Users") {
							$value = "<a class='moduleColor_$parentModule' href='?module=$parentModule&view=Detail&" .
								"record=$rawValue' title='" . getTranslatedString($parentModule, $parentModule) . "'>$value</a>";
						}
					} else {
						$value = '--';
					}
				} elseif ($field->getFieldDataType() == 'owner') {
					$value = textlength_check($this->ownerNameList[$fieldName][$value]);
				} elseif ($field->getUIType() == 25) {
					//TODO clean request object reference.
					$contactId = $_REQUEST['record'];
					$emailId = $this->db->query_result($result, $i, "activityid");
					$result1 = $this->db->pquery("SELECT access_count FROM vtiger_email_track WHERE " .
						"crmid=? AND mailid=?", array($contactId, $emailId));
					$value = $this->db->query_result($result1, 0, "access_count");
					if (!$value) {
						$value = 0;
					}
				} elseif ($field->getUIType() == 8) {
					if (!empty($value)) {
						$temp_val = html_entity_decode($value, ENT_QUOTES, $default_charset);
						$json = new Zend_Json();
						$value = vt_suppressHTMLTags(implode(',', $json->decode($temp_val)));
					}
				} elseif ($field->getFieldDataType() == 'taxes') {
					if (!empty($value)) {
						$valueArray = ($value != "") ? explode(',', $value) : [];
						$tmp = '';
						$tmpArray = [];
						$taxs = Vtiger_Taxs_UIType::getTaxes();
						foreach ($valueArray as $index => $tax) {
							if (isset($taxs[$tax])) {
								$tmpArray[] = $taxs[$tax]['value'] . '% - ' . $taxs[$tax]['name'];
							}
						}
						$value = implode(', ', $tmpArray);
						$value = Vtiger_Functions::textLength($value);
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
						$value = Vtiger_Functions::textLength($value);
					}
				} elseif ($field->getFieldDataType() == 'multiReferenceValue') {
					$params = $field->getFieldParams();
					$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($params['field']);
					$valueTmp = trim($value, '|#|');
					$valueTmp = ($valueTmp != "") ? explode('|#|', $valueTmp) : [];
					foreach ($valueTmp as $index => $tmp) {
						$valueTmp[$index] = $fieldModel->getUITypeModel()->getDisplayValue($tmp);
					}
					$value = implode(', ', $valueTmp);
					$value = Vtiger_Functions::textLength($value);
				} elseif (in_array($uitype, array(7, 9, 90))) {
					$value = "<span align='right'>" . textlength_check($value) . "</div>";
				} else {
					$value = Vtiger_Functions::textLength($value);
				}

//				// vtlib customization: For listview javascript triggers
//				$value = "$value <span type='vtlib_metainfo' vtrecordid='{$recordId}' vtfieldname=".
//					"'{$fieldName}' vtmodule='$module' style='display:none;'></span>";
//				// END
				$row[$fieldName] = $value;
			}
			$data[$recordId] = $row;
		}
		return $data;
	}
}

?>
