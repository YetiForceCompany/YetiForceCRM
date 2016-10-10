<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
require_once('include/CRMEntity.php');
require_once('include/utils/utils.php');
require_once 'include/Webservices/Utils.php';
global $adv_filter_options;

$adv_filter_options = array(
	'e' => 'equals',
	'n' => 'not equal to',
	's' => 'starts with',
	'ew' => 'ends with',
	'c' => 'contains',
	'k' => 'does not contain',
	'l' => 'less than',
	'g' => 'greater than',
	'm' => 'less or equal',
	'h' => 'greater or equal',
	'b' => 'before',
	'a' => 'after',
	'bw' => 'between',
);

class CustomView extends CRMEntity
{

	var $module_list = [];
	var $customviewmodule;
	var $list_fields;
	var $list_fields_name;
	var $setdefaultviewid;
	var $escapemodule;
	var $mandatoryvalues;
	var $showvalues;
	var $data_type;
	// Information as defined for this instance in the database table.
	protected $_status = false;
	protected $_userid = false;
	protected $meta;
	protected $moduleMetaInfo;

	/** This function sets the currentuser id to the class variable smownerid,
	 * modulename to the class variable customviewmodule
	 * @param $module -- The module Name:: Type String(optional)
	 * @returns  nothing
	 */
	public function __construct($module = '')
	{
		global $current_user;
		$this->customviewmodule = $module;
		$this->escapemodule[] = $module . '_';
		$this->escapemodule[] = '_';
		$this->smownerid = $current_user->id;
		$this->moduleMetaInfo = [];
		if ($module != '' && $module != 'Calendar') {
			$this->meta = $this->getMeta($module, $current_user);
		}
	}

	/**
	 *
	 * @param String:ModuleName $module
	 * @return EntityMeta
	 */
	public function getMeta($module, $user)
	{
		if (empty($this->moduleMetaInfo[$module])) {
			$handler = vtws_getModuleHandlerFromName($module, $user);
			$meta = $handler->getMeta();
			$this->moduleMetaInfo[$module] = $meta;
		}
		return $this->moduleMetaInfo[$module];
	}

	/** To get the customViewId of the specified module
	 * @param $module -- The module Name:: Type String
	 * @returns  customViewId :: Type Integer
	 */
	public function getViewId($module)
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . " ($module) method ...");
		$nowAction = AppRequest::get('view');
		if (AppRequest::isEmpty('viewname')) {
			if (isset($_SESSION['lvs'][$module]['viewname']) && $_SESSION['lvs'][$module]['viewname'] != '') {
				$viewid = $_SESSION['lvs'][$module]['viewname'];
			} elseif ($this->setdefaultviewid != '') {
				$viewid = $this->setdefaultviewid;
			} else {
				$viewid = $this->getDefaultCvId($module);
			}
			if (empty($viewid) || $this->isPermittedCustomView($viewid, $nowAction, $module) != 'yes') {
				$viewid = $this->getMandatoryFilter($module);
			}
		} else {
			$viewname = AppRequest::get('viewname');
			if (!is_numeric($viewname)) {
				$viewid = $this->getViewIdByName($viewname, $module);
				if (!$viewid) {
					$viewid = $this->getDefaultCvId($module);
				}
			} else {
				$viewid = $viewname;
			}
			if ($this->isPermittedCustomView($viewid, $nowAction, $module) != 'yes')
				$viewid = 0;
		}
		ListViewSession::setCurrentView($module, $viewid);
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $viewid;
	}

	public function getMandatoryFilter($module)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT cvid FROM vtiger_customview WHERE presence = ? and entitytype = ?;', [0, $module]);
		return $db->getSingleValue($result);
	}

	public function getDefaultCvId($module)
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$tabId = vtlib\Functions::getModuleId($module);

		$sql = 'SELECT userid, default_cvid FROM vtiger_user_module_preferences WHERE `tabid` = ?';
		$result = $db->pquery($sql, [$tabId]);
		$data = $result->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
		$user = 'Users:' . $currentUser->getId();
		if (array_key_exists($user, $data)) {
			return $data[$user][0];
		}
		if ($currentUser->isAdminUser()) {
			$userGroups = $currentUser->getUserGroups($currentUser->getId());
			$parentRoles = getRoleInformation($currentUser->getRole());
			$parentRoles = $parentRoles['parentrole'] ? $parentRoles['parentrole'] : [];
		} else {
			$parentRoles = $currentUser->getParentRoleSequence();
			$userGroups = $currentUser->get('privileges')->get('groups');
		}
		foreach ($userGroups as $groupId) {
			$group = 'Groups:' . $groupId;
			if (array_key_exists($group, $data)) {
				return $data[$group][0];
			}
		}
		$role = 'Roles:' . $currentUser->getRole();
		if (array_key_exists($role, $data)) {
			return $data[$role][0];
		}
		foreach (explode('::', $parentRoles) as $roleId) {
			$role = 'RoleAndSubordinates:' . $roleId;
			if (array_key_exists($role, $data)) {
				return $data[$role][0];
			}
		}
		$query = 'select cvid from vtiger_customview where setdefault = ? and entitytype = ?';
		$result = $db->pquery($query, [1, $module]);
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $db->getSingleValue($result);
	}

	public function getViewIdByName($viewname, $module)
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		$db = PearDatabase::getInstance();
		if (!empty($viewname)) {
			$query = "select cvid from vtiger_customview where viewname = ? and entitytype = ?";
			$result = $db->pquery($query, [$viewname, $module]);
			$viewid = $db->getSingleValue($result);
			return $viewid;
		} else {
			return 0;
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}

	// return type array
	/** to get the details of a customview
	 * @param $cvid :: Type Integer
	 * @returns  $customviewlist Array in the following format
	 * $customviewlist = Array('viewname'=>value,
	 *                         'setdefault'=>defaultchk,
	 *                         'setmetrics'=>setmetricschk)
	 */
	public function getCustomViewByCvid($cvid)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$tabid = \includes\Modules::getModuleId($this->customviewmodule);

		require('user_privileges/user_privileges_' . $current_user->id . '.php');

		$ssql = "select vtiger_customview.* from vtiger_customview inner join vtiger_tab on vtiger_tab.name = vtiger_customview.entitytype";
		$ssql .= " where vtiger_customview.cvid=?";
		$sparams = array($cvid);

		if ($is_admin == false) {
			$ssql .= " and (vtiger_customview.status=0 or vtiger_customview.userid = ? or vtiger_customview.status = 3 or vtiger_customview.userid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'))";
			array_push($sparams, $current_user->id);
		}
		$result = $adb->pquery($ssql, $sparams);

		$usercv_result = $adb->pquery("select default_cvid from vtiger_user_module_preferences where userid = ? and tabid = ?", array($current_user->id, $tabid));
		$def_cvid = $adb->query_result($usercv_result, 0, 'default_cvid');

		while ($cvrow = $adb->fetch_array($result)) {
			$customviewlist["viewname"] = $cvrow["viewname"];
			if ((isset($def_cvid) || $def_cvid != '') && $def_cvid == $cvid) {
				$customviewlist["setdefault"] = 1;
			} else {
				$customviewlist["setdefault"] = $cvrow["setdefault"];
			}
			$customviewlist["setmetrics"] = $cvrow["setmetrics"];
			$customviewlist["userid"] = $cvrow["userid"];
			$customviewlist["status"] = $cvrow["status"];
		}
		return $customviewlist;
	}

	/** to get the customviewCombo for the class variable customviewmodule
	 * @param $viewid :: Type Integer
	 * $viewid will make the corresponding selected
	 * @returns  $customviewCombo :: Type String
	 */
	public function getCustomViewCombo($viewid = '', $markselected = true)
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$tabid = \includes\Modules::getModuleId($this->customviewmodule);

		require('user_privileges/user_privileges_' . $current_user->id . '.php');

		$shtml_user = '';
		$shtml_pending = '';
		$shtml_public = '';
		$shtml_others = '';

		$selected = 'selected';
		if ($markselected == false)
			$selected = '';

		$ssql = "select vtiger_customview.*, vtiger_users.first_name,vtiger_users.last_name from vtiger_customview inner join vtiger_tab on vtiger_tab.name = vtiger_customview.entitytype
					left join vtiger_users on vtiger_customview.userid = vtiger_users.id ";
		$ssql .= " where vtiger_tab.tabid=?";
		$sparams = array($tabid);

		if ($is_admin == false) {
			$ssql .= " and (vtiger_customview.status=0 or vtiger_customview.userid = ? or vtiger_customview.status = 3 or vtiger_customview.userid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'))";
			array_push($sparams, $current_user->id);
		}
		$ssql .= " ORDER BY viewname";
		$result = $adb->pquery($ssql, $sparams);
		while ($cvrow = $adb->fetch_array($result)) {
			if ($cvrow['viewname'] == 'All') {
				$cvrow['viewname'] = \includes\Language::translate('COMBO_ALL');
			}

			$option = '';
			$viewname = $cvrow['viewname'];
			if ($cvrow['status'] == CV_STATUS_DEFAULT || $cvrow['userid'] == $current_user->id) {
				$disp_viewname = $viewname;
			} else {
				$userName = \vtlib\Deprecated::getFullNameFromArray('Users', $cvrow);
				$disp_viewname = $viewname . " [" . $userName . "] ";
			}


			if ($cvrow['setdefault'] == 1 && $viewid == '') {
				$option = "<option $selected value=\"" . $cvrow['cvid'] . "\">" . $disp_viewname . "</option>";
				$this->setdefaultviewid = $cvrow['cvid'];
			} elseif ($cvrow['cvid'] == $viewid) {
				$option = "<option $selected value=\"" . $cvrow['cvid'] . "\">" . $disp_viewname . "</option>";
				$this->setdefaultviewid = $cvrow['cvid'];
			} else {
				$option = "<option value=\"" . $cvrow['cvid'] . "\">" . $disp_viewname . "</option>";
			}

			// Add the option to combo box at appropriate section
			if ($option != '') {
				if ($cvrow['status'] == CV_STATUS_DEFAULT || $cvrow['userid'] == $current_user->id) {
					$shtml_user .= $option;
				} elseif ($cvrow['status'] == CV_STATUS_PUBLIC) {
					if ($shtml_public == '')
						$shtml_public = "<option disabled>--- " . \includes\Language::translate('LBL_PUBLIC') . " ---</option>";
					$shtml_public .= $option;
				} elseif ($cvrow['status'] == CV_STATUS_PENDING) {
					if ($shtml_pending == '')
						$shtml_pending = "<option disabled>--- " . \includes\Language::translate('LBL_PENDING') . " ---</option>";
					$shtml_pending .= $option;
				} else {
					if ($shtml_others == '')
						$shtml_others = "<option disabled>--- " . \includes\Language::translate('LBL_OTHERS') . " ---</option>";
					$shtml_others .= $option;
				}
			}
		}
		$shtml = $shtml_user;
		if ($is_admin == true)
			$shtml .= $shtml_pending;
		$shtml = $shtml . $shtml_public . $shtml_others;
		return $shtml;
	}

	/** to get the getColumnsListbyBlock for the given module and Block
	 * @param $module :: Type String
	 * @param $block :: Type Integer
	 * @returns  $columnlist Array in the format
	 * $columnlist = Array ($fieldlabel =>'$fieldtablename:$fieldcolname:$fieldname:$module_$fieldlabel1:$fieldtypeofdata',
	  $fieldlabel1 =>'$fieldtablename1:$fieldcolname1:$fieldname1:$module_$fieldlabel11:$fieldtypeofdata1',
	  |
	  $fieldlabeln =>'$fieldtablenamen:$fieldcolnamen:$fieldnamen:$module_$fieldlabel1n:$fieldtypeofdatan')
	 */
	public function getColumnsListbyBlock($module, $block)
	{
		$adb = PearDatabase::getInstance();
		$block_ids = explode(",", $block);
		$tabid = \includes\Modules::getModuleId($module);
		$current_user = vglobal('current_user');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		if (empty($this->meta) && $module != 'Calendar') {
			$this->meta = $this->getMeta($module, $current_user);
		}
		if ($tabid == 9)
			$tabid = "9,16";
		$display_type = " vtiger_field.displaytype in (1,2,3)";

		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
			$tab_ids = explode(",", $tabid);
			$sql = 'select * from vtiger_field ';
			$sql.= ' where vtiger_field.tabid in (%s) and vtiger_field.block in (%s) and vtiger_field.presence in (0,2) and';
			$sql.= $display_type;
			if ($tabid == 9 || $tabid == 16) {
				$sql.= " and vtiger_field.fieldname not in('notime','duration_minutes','duration_hours')";
			}
			$sql.= " order by sequence";
			$params = array($tab_ids, $block_ids);
		} else {
			$tab_ids = explode(",", $tabid);
			$profileList = getCurrentUserProfileList();
			$sql = 'select * from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid ';
			$sql.= ' where vtiger_field.tabid in (%s) and vtiger_field.block in (%s) and';
			$sql.= $display_type . 'and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)';

			$params = array($tab_ids, $block_ids);

			if (count($profileList) > 0) {
				$sql.= "  and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
				array_push($params, $profileList);
			}
			if ($tabid == 9 || $tabid == 16) {
				$sql.= " and vtiger_field.fieldname not in('notime','duration_minutes','duration_hours')";
			}

			$sql.= " group by columnname order by sequence";
		}
		if ($tabid == '9,16')
			$tabid = "9";
		$sql = sprintf($sql, generateQuestionMarks($tab_ids), generateQuestionMarks($block_ids));
		$result = $adb->pquery($sql, $params);
		$noofrows = $adb->num_rows($result);
		//Added on 14-10-2005 -- added ticket id in list
		if ($module == 'HelpDesk' && $block == 25) {
			$module_columnlist['vtiger_crmentity:crmid::HelpDesk_Ticket_ID:I'] = 'Ticket ID';
		}
		//Added to include vtiger_activity type in vtiger_activity vtiger_customview list
		if ($module == 'Calendar' && $block == 19) {
			$module_columnlist['vtiger_activity:activitytype:activitytype:Calendar_Activity_Type:V'] = 'Activity Type';
		}
		if ($module != 'Calendar') {
			$moduleFieldList = $this->meta->getModuleFields();
		}
		for ($i = 0; $i < $noofrows; $i++) {
			$fieldtablename = $adb->query_result($result, $i, "tablename");
			$fieldcolname = $adb->query_result($result, $i, "columnname");
			$fieldname = $adb->query_result($result, $i, "fieldname");
			$fieldtype = $adb->query_result($result, $i, "typeofdata");
			$fieldtype = explode("~", $fieldtype);
			$fieldtypeofdata = $fieldtype[0];
			$fieldlabel = $adb->query_result($result, $i, "fieldlabel");
			$field = $moduleFieldList[$fieldname];
			if (!empty($field) && $field->getFieldDataType() == 'reference') {
				$fieldtypeofdata = 'V';
			} else {
				//Here we Changing the displaytype of the field. So that its criteria will be
				//displayed Correctly in Custom view Advance Filter.
				$fieldtypeofdata = \vtlib\Functions::transformFieldTypeOfData($fieldtablename, $fieldcolname, $fieldtypeofdata);
			}
			if ($fieldlabel == "Start Date & Time") {
				$fieldlabel = "Start Date";
			}
			$fieldlabel1 = str_replace(" ", "_", $fieldlabel);
			$optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $fieldname . ":" . $module . "_" .
				$fieldlabel1 . ":" . $fieldtypeofdata;
			//added to escape attachments fields in customview as we have multiple attachments
			$fieldlabel = \includes\Language::translate($fieldlabel); //added to support i18n issue
			if ($module != 'HelpDesk' || $fieldname != 'filename')
				$module_columnlist[$optionvalue] = $fieldlabel;
			if ($fieldtype[1] == "M") {
				$this->mandatoryvalues[] = "'" . $optionvalue . "'";
				$this->showvalues[] = $fieldlabel;
				$this->data_type[$fieldlabel] = $fieldtype[1];
			}
		}
		return $module_columnlist;
	}

	/** to get the getModuleColumnsList for the given module
	 * @param $module :: Type String
	 * @returns  $ret_module_list Array in the following format
	 * $ret_module_list =
	  Array ('module' =>
	  Array('BlockLabel1' =>
	  Array('$fieldtablename:$fieldcolname:$fieldname:$module_$fieldlabel1:$fieldtypeofdata'=>$fieldlabel,
	  Array('$fieldtablename1:$fieldcolname1:$fieldname1:$module_$fieldlabel11:$fieldtypeofdata1'=>$fieldlabel1,
	  Array('BlockLabel2' =>
	  Array('$fieldtablename:$fieldcolname:$fieldname:$module_$fieldlabel1:$fieldtypeofdata'=>$fieldlabel,
	  Array('$fieldtablename1:$fieldcolname1:$fieldname1:$module_$fieldlabel11:$fieldtypeofdata1'=>$fieldlabel1,
	  |
	  Array('BlockLabeln' =>
	  Array('$fieldtablename:$fieldcolname:$fieldname:$module_$fieldlabel1:$fieldtypeofdata'=>$fieldlabel,
	  Array('$fieldtablename1:$fieldcolname1:$fieldname1:$module_$fieldlabel11:$fieldtypeofdata1'=>$fieldlabel1,


	 */
	public function getModuleColumnsList($module)
	{

		$module_info = $this->getCustomViewModuleInfo($module);
		foreach ($this->module_list[$module] as $key => $value) {
			$columnlist = $this->getColumnsListbyBlock($module, $value);

			if (isset($columnlist)) {
				$ret_module_list[$module][$key] = $columnlist;
			}
		}
		return $ret_module_list;
	}

	/** to get the getModuleColumnsList for the given customview
	 * @param $cvid :: Type Integer
	 * @returns  $columnlist Array in the following format
	 * $columnlist = Array( $columnindex => $columnname,
	 * 			 $columnindex1 => $columnname1,
	 * 					|
	 * 			 $columnindexn => $columnnamen)
	 */
	public function getColumnsListByCvid($cvid)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . ' method ...');

		$sSQL = 'select vtiger_cvcolumnlist.* from vtiger_cvcolumnlist';
		$sSQL .= ' inner join vtiger_customview on vtiger_customview.cvid = vtiger_cvcolumnlist.cvid';
		$sSQL .= ' where vtiger_customview.cvid =? order by vtiger_cvcolumnlist.columnindex';
		$result = $adb->pquery($sSQL, [$cvid]);

		if ($adb->num_rows($result) == 0 && is_numeric($cvid) && $this->customviewmodule != 'Users') {
			$log->debug("Error !!!: " . vtranslate('LBL_NO_FOUND_VIEW') . " ID: $cvid");
			die(vtlib\Functions::throwNewException(vtranslate('LBL_NO_FOUND_VIEW')));
		} else if (!is_numeric($cvid) && $this->customviewmodule != 'Users') {
			$filterDir = 'modules' . DIRECTORY_SEPARATOR . $this->customviewmodule . DIRECTORY_SEPARATOR . 'filters' . DIRECTORY_SEPARATOR . $cvid . '.php';
			if (file_exists($filterDir)) {
				$handlerClass = Vtiger_Loader::getComponentClassName('Filter', $cvid, $this->customviewmodule);
				if (class_exists($handlerClass)) {
					$handler = new $handlerClass();
					$columnlist = $handler->getColumnList();
				}
			} else {
				$log->debug("Error !!!: " . vtranslate('LBL_NO_FOUND_VIEW') . " Filter: $cvid");
				die(vtlib\Functions::throwNewException(vtranslate('LBL_NO_FOUND_VIEW')));
			}
		} else {
			while ($columnrow = $adb->fetch_array($result)) {
				$columnlist[$columnrow['columnindex']] = $columnrow['columnname'];
			}
		}
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
		return $columnlist;
	}

	/** to get the standard filter fields or the given module
	 * @param $module :: Type String
	 * @returns  $stdcriteria_list Array in the following format
	 * $stdcriteria_list = Array( $tablename:$columnname:$fieldname:$module_$fieldlabel => $fieldlabel,
	 * 			 $tablename1:$columnname1:$fieldname1:$module_$fieldlabel1 => $fieldlabel1,
	 * 					|
	 * 			 $tablenamen:$columnnamen:$fieldnamen:$module_$fieldlabeln => $fieldlabeln)
	 */
	public function getStdCriteriaByModule($module)
	{
		$adb = PearDatabase::getInstance();
		$tabid = \includes\Modules::getModuleId($module);

		$current_user = vglobal('current_user');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');

		$module_info = $this->getCustomViewModuleInfo($module);
		foreach ($this->module_list[$module] as $key => $blockid) {
			$blockids[] = $blockid;
		}

		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
			$sql = 'select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid ';
			$sql.= ' where vtiger_field.tabid=? and vtiger_field.block in (%s)
                        and vtiger_field.uitype in (5,6,23,70)';
			$sql.= ' and vtiger_field.presence in (0,2) order by vtiger_field.sequence';
			$params = [$tabid, $blockids];
		} else {
			$profileList = getCurrentUserProfileList();
			$sql = 'select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid inner join  vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid ';
			$sql.= ' where vtiger_field.tabid=? and vtiger_field.block in (%s) and vtiger_field.uitype in (5,6,23,70)';
			$sql.= " and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			$params = [$tabid, $blockids];

			if (count($profileList) > 0) {
				$sql.= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
				array_push($params, $profileList);
			}

			$sql.= " order by vtiger_field.sequence";
		}
		$sql = sprintf($sql, generateQuestionMarks($block_ids));
		$result = $adb->pquery($sql, $params);

		while ($criteriatyperow = $adb->fetch_array($result)) {
			$fieldtablename = $criteriatyperow["tablename"];
			$fieldcolname = $criteriatyperow["columnname"];
			$fieldlabel = $criteriatyperow["fieldlabel"];
			$fieldname = $criteriatyperow["fieldname"];
			$fieldlabel1 = str_replace(" ", "_", $fieldlabel);
			$optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $fieldname . ":" . $module . "_" . $fieldlabel1;
			$stdcriteria_list[$optionvalue] = $fieldlabel;
		}

		return $stdcriteria_list;
	}

	/**
	 *  Function which will give condition list for date fields
	 * @return array of std filter conditions
	 */
	public function getStdFilterConditions()
	{
		return Array("custom", "prevfy", "thisfy", "nextfy", "prevfq",
			"thisfq", "nextfq", "yesterday", "today", "tomorrow",
			"lastweek", "thisweek", "nextweek", "lastmonth", "thismonth",
			"nextmonth", "last7days", "last30days", "last60days", "last90days",
			"last120days", "next30days", "next60days", "next90days", "next120days",
		);
	}

	/** to get the standard filter for the given customview Id
	 * @param $cvid :: Type Integer
	 * @returns  $stdfilterlist Array in the following format
	 * $stdfilterlist = Array( 'columnname' =>  $tablename:$columnname:$fieldname:$module_$fieldlabel,'stdfilter'=>$stdfilter,'startdate'=>$startdate,'enddate'=>$enddate)
	 */
	public function getStdFilterByCvid($cvid)
	{
		$stdFilter = Vtiger_Cache::get('getStdFilterByCvid', $cvid);
		if ($stdFilter !== false) {
			return $stdFilter;
		}

		if (is_numeric($cvid)) {
			$adb = PearDatabase::getInstance();
			$sSQL = 'select vtiger_cvstdfilter.* from vtiger_cvstdfilter inner join vtiger_customview on vtiger_customview.cvid = vtiger_cvstdfilter.cvid where vtiger_cvstdfilter.cvid=?';
			$result = $adb->pquery($sSQL, array($cvid));
			$stdfilterrow = $adb->fetch_array($result);
		} else {
			$filterDir = 'modules' . DIRECTORY_SEPARATOR . $this->customviewmodule . DIRECTORY_SEPARATOR . 'filters' . DIRECTORY_SEPARATOR . $cvid . '.php';
			if (file_exists($filterDir)) {
				$handlerClass = Vtiger_Loader::getComponentClassName('Filter', $cvid, $this->customviewmodule);
				if (class_exists($handlerClass)) {
					$handler = new $handlerClass();
					$stdfilterrow = $handler->getStdCriteria();
				}
			}
		}
		$stdFilter = $this->resolveDateFilterValue($stdfilterrow);
		Vtiger_Cache::set('getStdFilterByCvid', $cvid, $stdFilter);
		return $stdFilter;
	}

	public function resolveDateFilterValue($dateFilterRow)
	{
		$stdfilterlist = [];
		$stdfilterlist['columnname'] = $dateFilterRow['columnname'];
		$stdfilterlist['stdfilter'] = $dateFilterRow['stdfilter'];

		if ($dateFilterRow['stdfilter'] == 'custom' || $dateFilterRow['stdfilter'] == '' || $dateFilterRow['stdfilter'] == 'e' || $dateFilterRow['stdfilter'] == 'n') {
			if ($dateFilterRow['startdate'] != '0000-00-00' && $dateFilterRow['startdate'] != '') {
				$startDateTime = new DateTimeField($dateFilterRow['startdate'] . ' ' . date('H:i:s'));
				$stdfilterlist['startdate'] = $startDateTime->getDisplayDate();
			}
			if ($dateFilterRow['enddate'] != '0000-00-00' && $dateFilterRow['enddate'] != '') {
				$endDateTime = new DateTimeField($dateFilterRow['enddate'] . ' ' . date('H:i:s'));
				$stdfilterlist['enddate'] = $endDateTime->getDisplayDate();
			}
		} else { //if it is not custom get the date according to the selected duration
			$datefilter = $this->getDateforStdFilterBytype($dateFilterRow['stdfilter']);
			$startDateTime = new DateTimeField($datefilter[0] . ' ' . date('H:i:s'));
			$stdfilterlist['startdate'] = $startDateTime->getDisplayDate();
			$endDateTime = new DateTimeField($datefilter[1] . ' ' . date('H:i:s'));
			$stdfilterlist['enddate'] = $endDateTime->getDisplayDate();
		}
		return $stdfilterlist;
	}

	/** to get the Advanced filter for the given customview Id
	 * @param $cvid :: Type Integer
	 * @returns  $advfilterlist Array
	 */
	public function getAdvFilterByCvid($cvid)
	{
		$adb = PearDatabase::getInstance();
		$default_charset = vglobal('default_charset');
		$advft_criteria = [];

		$sql = 'SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid = ? ORDER BY groupid';
		$groupsresult = $adb->pquery($sql, [$cvid]);

		$i = 1;
		$j = 0;
		while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
			$groupId = $relcriteriagroup["groupid"];
			$groupCondition = $relcriteriagroup["group_condition"];

			$ssql = 'select vtiger_cvadvfilter.* from vtiger_customview
						inner join vtiger_cvadvfilter on vtiger_cvadvfilter.cvid = vtiger_customview.cvid
						left join vtiger_cvadvfilter_grouping on vtiger_cvadvfilter.cvid = vtiger_cvadvfilter_grouping.cvid
								and vtiger_cvadvfilter.groupid = vtiger_cvadvfilter_grouping.groupid';
			$ssql.= " where vtiger_customview.cvid = ? && vtiger_cvadvfilter.groupid = ? order by vtiger_cvadvfilter.columnindex";

			$result = $adb->pquery($ssql, array($cvid, $groupId));
			$noOfColumns = $adb->num_rows($result);
			if ($noOfColumns <= 0)
				continue;

			while ($relcriteriarow = $adb->fetch_array($result)) {
				$criteria = $this->getAdvftCriteria($relcriteriarow);
				$advft_criteria[$i]['columns'][$j] = $criteria;
				$advft_criteria[$i]['condition'] = $groupCondition;
				$j++;
			}

			if (!empty($advft_criteria[$i]['columns'][$j - 1]['column_condition'])) {
				$advft_criteria[$i]['columns'][$j - 1]['column_condition'] = '';
			}
			$i++;
		}

		if (!is_numeric($cvid)) {
			$filterDir = 'modules' . DIRECTORY_SEPARATOR . $this->customviewmodule . DIRECTORY_SEPARATOR . 'filters' . DIRECTORY_SEPARATOR . $cvid . '.php';
			if (file_exists($filterDir)) {
				$handlerClass = Vtiger_Loader::getComponentClassName('Filter', $cvid, $this->customviewmodule);
				if (class_exists($handlerClass)) {
					$handler = new $handlerClass();
					$advftCriteria = $handler->getAdvftCriteria($this);
					$i = $advftCriteria[0];
					$j = $advftCriteria[1];
					$advft_criteria = $advftCriteria[2];
				}
			}
		}

		// Clear the condition (and/or) for last group, if any.
		if (!empty($advft_criteria[$i - 1]['condition']))
			$advft_criteria[$i - 1]['condition'] = '';

		return $advft_criteria;
	}

	public function getAdvftCriteria($relcriteriarow)
	{
		$columnIndex = $relcriteriarow['columnindex'];
		$criteria = [];
		$criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"], ENT_QUOTES, $default_charset);
		$criteria['comparator'] = $relcriteriarow["comparator"];
		$advfilterval = html_entity_decode($relcriteriarow["value"], ENT_QUOTES, $default_charset);
		$col = explode(":", $relcriteriarow["columnname"]);
		$temp_val = explode(",", $relcriteriarow["value"]);
		if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
			$val = [];
			for ($x = 0; $x < count($temp_val); $x++) {
				if ($col[4] == 'D') {
					/** while inserting in db for due_date it was taking date and time values also as it is
					 * date time field. We only need to take date from that value
					 */
					if ($col[0] == "vtiger_activity" && $col[1] == "due_date") {
						$values = explode(' ', $temp_val[$x]);
						$temp_val[$x] = $values[0];
					}
					$date = new DateTimeField(trim($temp_val[$x]));
					$val[$x] = $date->getDisplayDate();
				} elseif ($col[4] == 'DT') {
					$comparator = array('e', 'n', 'b', 'a');
					if (in_array($criteria['comparator'], $comparator)) {
						$originalValue = $temp_val[$x];
						$dateTime = explode(' ', $originalValue);
						$temp_val[$x] = $dateTime[0];
					}
					$date = new DateTimeField(trim($temp_val[$x]));
					$val[$x] = $date->getDisplayDateTimeValue();
				} else {
					$date = new DateTimeField(trim($temp_val[$x]));
					$val[$x] = $date->getDisplayTime();
				}
			}
			$advfilterval = implode(",", $val);
		}
		$criteria['value'] = $advfilterval;
		$criteria['column_condition'] = $relcriteriarow["column_condition"];

		return $criteria;
	}

	/**
	 * Cache information to perform re-lookups
	 *
	 * @var String
	 */
	protected $_fieldby_tblcol_cache = [];

	/**
	 * Function to check if field is present based on
	 *
	 * @param String $columnname
	 * @param String $tablename
	 */
	public function isFieldPresent_ByColumnTable($columnname, $tablename)
	{
		$adb = PearDatabase::getInstance();

		if (!isset($this->_fieldby_tblcol_cache[$tablename])) {
			$query = 'SELECT columnname FROM vtiger_field WHERE tablename = ? and presence in (0,2)';

			$result = $adb->pquery($query, array($tablename));
			$numrows = $adb->num_rows($result);

			if ($numrows) {
				$this->_fieldby_tblcol_cache[$tablename] = [];
				for ($index = 0; $index < $numrows; ++$index) {
					$this->_fieldby_tblcol_cache[$tablename][] = $adb->query_result($result, $index, 'columnname');
				}
			}
		}
		// If still the field was not found (might be disabled or deleted?)
		if (!isset($this->_fieldby_tblcol_cache[$tablename])) {
			return false;
		}
		return in_array($columnname, $this->_fieldby_tblcol_cache[$tablename]);
	}

	/** to get the customview Columnlist Query for the given customview Id
	 * @param $cvid :: Type Integer
	 * @returns  $getCvColumnList as a string
	 * This function will return the columns for the given customfield in comma seperated values in the format
	 *                     $tablename.$columnname,$tablename1.$columnname1, ------ $tablenamen.$columnnamen
	 *
	 */
	public function getCvColumnListSQL($cvid)
	{
		$adb = PearDatabase::getInstance();
		$columnslist = $this->getColumnsListByCvid($cvid);
		if (isset($columnslist)) {
			foreach ($columnslist as $columnname => $value) {
				$tablefield = "";
				if ($value != "") {
					$list = explode(":", $value);

					//Added For getting status for Activities -Jaguar
					$sqllist_column = $list[0] . "." . $list[1];
					if ($this->customviewmodule == "Calendar") {
						if ($list[1] == "status" || $list[1] == "activitystatus") {
							$sqllist_column = "vtiger_activity.status as activitystatus";
						}
					}
					//Added for assigned to sorting
					if ($list[1] == "smownerid") {
						$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
								'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
						$sqllist_column = "case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name";
					}
					if ($list[0] == "vtiger_contactdetails" && $list[1] == "lastname")
						$sqllist_column = "vtiger_contactdetails.lastname,vtiger_contactdetails.firstname";
					$sqllist[] = $sqllist_column;
					//Ends

					$tablefield[$list[0]] = $list[1];

					//Changed as the replace of module name may replace the string if the fieldname has module name in it -- Jeri
					$fieldinfo = explode('_', $list[3], 2);
					$fieldlabel = $fieldinfo[1];
					$fieldlabel = str_replace("_", " ", $fieldlabel);

					if ($this->isFieldPresent_ByColumnTable($list[1], $list[0])) {

						$this->list_fields[$fieldlabel] = $tablefield;
						$this->list_fields_name[$fieldlabel] = $list[2];
					}
				}
			}
			$returnsql = implode(",", $sqllist);
		}
		return $returnsql;
	}

	/** to get the customview stdFilter Query for the given customview Id
	 * @param $cvid :: Type Integer
	 * @returns  $stdfiltersql as a string
	 * This function will return the standard filter criteria for the given customfield
	 *
	 */
	public function getCVStdFilterSQL($cvid)
	{
		$adb = PearDatabase::getInstance();

		$stdfiltersql = '';
		$stdfilterlist = [];

		$sSQL = "select vtiger_cvstdfilter.* from vtiger_cvstdfilter inner join vtiger_customview on vtiger_customview.cvid = vtiger_cvstdfilter.cvid";
		$sSQL .= " where vtiger_cvstdfilter.cvid=?";

		$result = $adb->pquery($sSQL, array($cvid));
		$stdfilterrow = $adb->fetch_array($result);

		$stdfilterlist = [];
		$stdfilterlist["columnname"] = $stdfilterrow["columnname"];
		$stdfilterlist["stdfilter"] = $stdfilterrow["stdfilter"];

		if ($stdfilterrow["stdfilter"] == "custom" || $stdfilterrow["stdfilter"] == "") {
			if ($stdfilterrow["startdate"] != "0000-00-00" && $stdfilterrow["startdate"] != "") {
				$stdfilterlist["startdate"] = $stdfilterrow["startdate"];
			}
			if ($stdfilterrow["enddate"] != "0000-00-00" && $stdfilterrow["enddate"] != "") {
				$stdfilterlist["enddate"] = $stdfilterrow["enddate"];
			}
		} else { //if it is not custom get the date according to the selected duration
			$datefilter = $this->getDateforStdFilterBytype($stdfilterrow["stdfilter"]);
			$stdfilterlist["startdate"] = $datefilter[0];
			$stdfilterlist["enddate"] = $datefilter[1];
		}

		if (isset($stdfilterlist)) {

			foreach ($stdfilterlist as $columnname => $value) {

				if ($columnname == "columnname") {
					$filtercolumn = $value;
				} elseif ($columnname == "stdfilter") {
					$filtertype = $value;
				} elseif ($columnname == "startdate") {
					$startDateTime = new DateTimeField($value . ' ' . date('H:i:s'));
					$userStartDate = $startDateTime->getDisplayDate();
					$userStartDateTime = new DateTimeField($userStartDate . ' 00:00:00');
					$startDateTime = $userStartDateTime->getDBInsertDateTimeValue();
				} elseif ($columnname == "enddate") {
					$endDateTime = new DateTimeField($value . ' ' . date('H:i:s'));
					$userEndDate = $endDateTime->getDisplayDate();
					$userEndDateTime = new DateTimeField($userEndDate . ' 23:59:00');
					$endDateTime = $userEndDateTime->getDBInsertDateTimeValue();
				}
				if ($startDateTime != "" && $endDateTime != "") {
					$columns = explode(":", $filtercolumn);
					// Fix for http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/5423
					if ($columns[1] == 'birthday') {
						$tableColumnSql = "DATE_FORMAT(" . $columns[0] . "." . $columns[1] . ", '%m%d')";
						$startDateTime = "DATE_FORMAT('$startDate', '%m%d')";
						$endDateTime = "DATE_FORMAT('$endDate', '%m%d')";
						$stdfiltersql = $tableColumnSql . " BETWEEN " . $startDateTime . " and " . $endDateTime;
					} else {
						if ($this->customviewmodule == 'Calendar' && ($columns[1] == 'date_start' || $columns[1] == 'due_date')) {
							$tableColumnSql = '';
							if ($columns[1] == 'date_start') {
								$tableColumnSql = "CAST((CONCAT(date_start,' ',time_start)) AS DATETIME)";
							} else {
								$tableColumnSql = "CAST((CONCAT(due_date,' ',time_end)) AS DATETIME)";
							}
						} else {
							$tableColumnSql = $columns[0] . "." . $columns[1];
						}
						$stdfiltersql = $tableColumnSql . " BETWEEN '" . $startDateTime . "' and '" . $endDateTime . "'";
					}
				}
			}
		}
		return $stdfiltersql;
	}

	/** to get the customview AdvancedFilter Query for the given customview Id
	 * @param $cvid :: Type Integer
	 * @returns  $advfiltersql as a string
	 * This function will return the advanced filter criteria for the given customfield
	 *
	 */
	// Needs to be modified according to the new advanced filter (support for grouping).
	// Not modified as of now, as this function is not used for now (Instead Query Generator is used for better performance).
	public function getCVAdvFilterSQL($cvid)
	{
		$current_user = vglobal('current_user');

		$advfilter = $this->getAdvFilterByCvid($cvid);

		$advcvsql = "";

		foreach ($advfilter as $groupid => $groupinfo) {

			$groupcolumns = $groupinfo["columns"];
			$groupcondition = $groupinfo["condition"];
			$advfiltergroupsql = "";

			foreach ($groupcolumns as $columnindex => $columninfo) {
				$columnname = $columninfo['columnname'];
				$comparator = $columninfo['comparator'];
				$value = $columninfo['value'];
				$columncondition = $columninfo['column_condition'];

				$columns = explode(":", $columnname);
				$datatype = (isset($columns[4])) ? $columns[4] : "";

				if ($columnname != "" && $comparator != "") {
					$valuearray = explode(",", trim($value));

					if (isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {
						$advorsql = "";
						for ($n = 0; $n < count($valuearray); $n++) {
							$advorsql[] = $this->getRealValues($columns[0], $columns[1], $comparator, trim($valuearray[$n]), $datatype);
						}
						//If negative logic filter ('not equal to', 'does not contain') is used, 'and' condition should be applied instead of 'or'
						if ($comparator == 'n' || $comparator == 'k')
							$advorsqls = implode(" and ", $advorsql);
						else
							$advorsqls = implode(" or ", $advorsql);
						$advfiltersql = " (" . $advorsqls . ") ";
					}
					elseif ($comparator == 'bw' && count($valuearray) == 2) {
						$advfiltersql = "(" . $columns[0] . "." . $columns[1] . " between '" . getValidDBInsertDateTimeValue(trim($valuearray[0]), $datatype) . "' and '" . getValidDBInsertDateTimeValue(trim($valuearray[1]), $datatype) . "')";
					} elseif ($comparator == 'y') {
						$advfiltersql = sprintf("(%s.%s IS NULL || %s.%s = '')", $columns[0], $columns[1], $columns[0], $columns[1]);
					} else {
						//Added for getting vtiger_activity Status -Jaguar
						if ($this->customviewmodule == "Calendar" && ($columns[1] == "status")) {
							$advfiltersql = "vtiger_activity.status" . $this->getAdvComparator($comparator, trim($value), $datatype);
						} elseif ($this->customviewmodule == "Assets") {
							if ($columns[1] == 'account') {
								$advfiltersql = "vtiger_account.accountname" . $this->getAdvComparator($comparator, trim($value), $datatype);
							}
							if ($columns[1] == 'product') {
								$advfiltersql = "vtiger_products.productname" . $this->getAdvComparator($comparator, trim($value), $datatype);
							}
						} else {
							$advfiltersql = $this->getRealValues($columns[0], $columns[1], $comparator, trim($value), $datatype);
						}
					}

					$advfiltergroupsql .= $advfiltersql;
					if ($columncondition != NULL && $columncondition != '' && count($groupcolumns) > $columnindex) {
						$advfiltergroupsql .= ' ' . $columncondition . ' ';
					}
				}
			}

			if (trim($advfiltergroupsql) != "") {
				$advfiltergroupsql = "( $advfiltergroupsql ) ";
				if ($groupcondition != NULL && $groupcondition != '' && $advfilter > $groupid) {
					$advfiltergroupsql .= ' ' . $groupcondition . ' ';
				}

				$advcvsql .= $advfiltergroupsql;
			}
		}
		if (trim($advcvsql) != "")
			$advcvsql = '(' . $advcvsql . ')';
		return $advcvsql;
	}

	/** to get the realvalues for the given value
	 * @param $tablename :: type string
	 * @param $fieldname :: type string
	 * @param $comparator :: type string
	 * @param $value :: type string
	 * @returns  $value as a string in the following format
	 * 	  $tablename.$fieldname comparator
	 */
	public function getRealValues($tablename, $fieldname, $comparator, $value, $datatype)
	{
		//we have to add the fieldname/tablename.fieldname and the corresponding value (which we want) we can add here. So that when these LHS field comes then RHS value will be replaced for LHS in the where condition of the query
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$currentModule = vglobal('currentModule');
		$mod_strings = vglobal('mod_strings');
		//Added for proper check of contact name in advance filter
		if ($tablename == "vtiger_contactdetails" && $fieldname == "lastname")
			$fieldname = "contactid";

		$contactid = "vtiger_contactdetails.lastname";
		if ($currentModule != "Contacts" && $currentModule != "Leads" && $currentModule != 'Campaigns') {
			$contactid = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('lastname' => 'vtiger_contactdetails.lastname', 'firstname' => 'vtiger_contactdetails.firstname'), 'Contacts');
		}
		$change_table_field = Array(
			"product_id" => "vtiger_products.productname",
			"contactid" => 'trim(' . $contactid . ')',
			"contact_id" => 'trim(' . $contactid . ')',
			"accountid" => "", //in cvadvfilter accountname is stored for Contact, Potential
			"account_id" => "", //Same like accountid. No need to change
			"vendorid" => "vtiger_vendor.vendorname",
			"vendor_id" => "vtiger_vendor.vendorname",
			"vtiger_account.parentid" => "vtiger_account2.accountname",
			"campaignid" => "vtiger_campaign.campaignname",
			"vtiger_contactdetails.reportsto" => \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('lastname' => 'vtiger_contactdetails2.lastname', 'firstname' => 'vtiger_contactdetails2.firstname'), 'Contacts'),
			"vtiger_pricebook.currency_id" => "vtiger_currency_info.currency_name",
		);

		if ($fieldname == "smownerid" || $fieldname == 'modifiedby') {
			if ($fieldname == "smownerid") {
				$tableNameSuffix = '';
			} elseif ($fieldname == "modifiedby") {
				$tableNameSuffix = '2';
			}
			$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' =>
					'vtiger_users' . $tableNameSuffix . '.first_name', 'last_name' => 'vtiger_users' . $tableNameSuffix . '.last_name'), 'Users');
			$temp_value = '( trim(' . $userNameSql . ')' . $this->getAdvComparator($comparator, $value, $datatype);
			$temp_value.= " ||  vtiger_groups$tableNameSuffix.groupname" . $this->getAdvComparator($comparator, $value, $datatype) . ')';
			$value = $temp_value; // Hot fix: removed unbalanced closing bracket ")";
		} elseif ($fieldname == "inventorymanager") {
			$value = $tablename . "." . $fieldname . $this->getAdvComparator($comparator, getUserId_Ol($value), $datatype);
		} elseif ($change_table_field[$fieldname] != '') {//Added to handle special cases
			$value = $change_table_field[$fieldname] . $this->getAdvComparator($comparator, $value, $datatype);
		} elseif ($change_table_field[$tablename . "." . $fieldname] != '') {//Added to handle special cases
			$tmp_value = '';
			if ((($comparator == 'e' || $comparator == 's' || $comparator == 'c') && trim($value) == '') || (($comparator == 'n' || $comparator == 'k') && trim($value) != '')) {
				$tmp_value = $change_table_field[$tablename . "." . $fieldname] . ' IS NULL or ';
			}
			$value = $tmp_value . $change_table_field[$tablename . "." . $fieldname] . $this->getAdvComparator($comparator, $value, $datatype);
		} elseif (($fieldname == "crmid" && $tablename != 'vtiger_crmentity') || $fieldname == "parent_id" || $fieldname == 'parentid') {
			//For crmentity.crmid the control should not come here. This is only to get the related to modules
			$value = $this->getSalesRelatedName($comparator, $value, $datatype, $tablename, $fieldname);
		} else {
			//For checkbox type values, we have to convert yes/no as 1/0 to get the values
			$field_uitype = getUItype($this->customviewmodule, $fieldname);
			if ($field_uitype == 56) {
				if (strtolower($value) == 'yes')
					$value = 1;
				elseif (strtolower($value) == 'no')
					$value = 0;
			} else if (is_uitype($field_uitype, '_picklist_')) { /* Fix for tickets 4465 and 4629 */
				// Get all the keys for the for the Picklist value
				$mod_keys = array_keys($mod_strings, $value);

				// Iterate on the keys, to get the first key which doesn't start with LBL_      (assuming it is not used in PickList)
				foreach ($mod_keys as $mod_idx => $mod_key) {
					$stridx = strpos($mod_key, 'LBL_');
					// Use strict type comparision, refer strpos for more details
					if ($stridx !== 0) {
						$value = $mod_key;
						break;
					}
				}
			}
			//added to fix the ticket
			if ($this->customviewmodule == "Calendar" && ($fieldname == "status" || $fieldname == "activitystatus")) {
				$value = " vtiger_activity.status " . $this->getAdvComparator($comparator, $value, $datatype);
			} elseif ($comparator == 'e' && (trim($value) == "NULL" || trim($value) == '')) {
				$value = '(' . $tablename . "." . $fieldname . ' IS NULL || ' . $tablename . "." . $fieldname . ' = \'\')';
			} else {
				$value = $tablename . "." . $fieldname . $this->getAdvComparator($comparator, $value, $datatype);
			}
			//end
		}
		return $value;
	}

	/** to get the related name for the given module
	 * @param $comparator :: type string,
	 * @param $value :: type string,
	 * @param $datatype :: type string,
	 * @returns  $value :: string
	 */
	public function getSalesRelatedName($comparator, $value, $datatype, $tablename, $fieldname)
	{
		$log = vglobal('log');
		$log->info("in getSalesRelatedName " . $comparator . "==" . $value . "==" . $datatype . "==" . $tablename . "==" . $fieldname);
		$adb = PearDatabase::getInstance();

		$adv_chk_value = $value;
		$value = '(';
		$sql = sprintf('select distinct(setype) from vtiger_crmentity c INNER JOIN %s t ON t.%s = c.crmid', $adb->sql_escape_string($tablename), $adb->sql_escape_string($fieldname));
		$res = $adb->query($sql);
		for ($s = 0; $s < $adb->num_rows($res); $s++) {
			$modulename = $adb->query_result($res, $s, "setype");
			if ($modulename == 'Vendors') {
				continue;
			}
			if ($s != 0)
				$value .= ' or ';
			if ($modulename == 'Accounts') {
				//By Pavani : Related to problem in calender, Ticket: 4284 and 4675
				if (($comparator == 'e' || $comparator == 's' || $comparator == 'c') && trim($adv_chk_value) == '') {
					$value .= 'vtiger_account.accountname IS NULL or ';
				}
				$value .= 'vtiger_account.accountname';
			}
			if ($modulename == 'Leads') {
				$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('lastname' => 'vtiger_leaddetails.lastname', 'firstname' => 'vtiger_leaddetails.firstname'), 'Leads');
				if (($comparator == 'e' || $comparator == 's' || $comparator == 'c') && trim($adv_chk_value) == '') {
					$value .= " $concatSql IS NULL or ";
				}
				$value .= " $concatSql";
			}
			if ($modulename == 'Products') {
				if (($comparator == 'e' || $comparator == 's' || $comparator == 'c') && trim($adv_chk_value) == '') {
					$value .= ' vtiger_products.productname IS NULL or ';
				}
				$value .= ' vtiger_products.productname';
			}
			if ($modulename == 'Contacts') {
				$concatSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('lastname' => 'vtiger_contactdetails.lastname', 'firstname' => 'vtiger_contactdetails.firstname'), 'Contacts');
				if (($comparator == 'e' || $comparator == 's' || $comparator == 'c') && trim($adv_chk_value) == '') {
					$value .= " $concatSql IS NULL or ";
				}
				$value .= " $concatSql";
			}
			if ($modulename == 'HelpDesk') {
				if (($comparator == 'e' || $comparator == 's' || $comparator == 'c') && trim($adv_chk_value) == '') {
					$value .= ' vtiger_troubletickets.title IS NULL or ';
				}
				$value .= ' vtiger_troubletickets.title';
			}
			if ($modulename == 'Campaigns') {
				if (($comparator == 'e' || $comparator == 's' || $comparator == 'c') && trim($adv_chk_value) == '') {
					$value .= ' vtiger_campaign.campaignname IS NULL or ';
				}
				$value .= ' vtiger_campaign.campaignname';
			}

			$value .= $this->getAdvComparator($comparator, $adv_chk_value, $datatype);
		}
		$value .= ")";
		$log->info("in getSalesRelatedName " . $comparator . "==" . $value . "==" . $datatype . "==" . $tablename . "==" . $fieldname);
		return $value;
	}

	/** to get the comparator value for the given comparator and value
	 * @param $comparator :: type string
	 * @param $value :: type string
	 * @returns  $rtvalue in the format $comparator $value
	 */
	public function getAdvComparator($comparator, $value, $datatype = '')
	{

		global $adb, $default_charset;
		$value = html_entity_decode(trim($value), ENT_QUOTES, $default_charset);
		$value = $adb->sql_escape_string($value);

		if ($comparator == "e") {
			if (trim($value) == "NULL") {
				$rtvalue = " is NULL";
			} elseif (trim($value) != "") {
				$rtvalue = " = " . $adb->quote($value);
			} elseif (trim($value) == "" && ($datatype == "V" || $datatype == "E")) {
				$rtvalue = " = " . $adb->quote($value);
			} else {
				$rtvalue = " is NULL";
			}
		}
		if ($comparator == "n") {
			if (trim($value) == "NULL") {
				$rtvalue = " is NOT NULL";
			} elseif (trim($value) != "") {
				$rtvalue = " <> " . $adb->quote($value);
			} elseif (trim($value) == "" && $datatype == "V") {
				$rtvalue = " <> " . $adb->quote($value);
			} elseif (trim($value) == "" && $datatype == "E") {
				$rtvalue = " <> " . $adb->quote($value);
			} else {
				$rtvalue = " is NOT NULL";
			}
		}
		if ($comparator == "s") {
			if (trim($value) == "" && ($datatype == "V" || $datatype == "E")) {
				$rtvalue = " like '" . formatForSqlLike($value, 3) . "'";
			} else {
				$rtvalue = " like '" . formatForSqlLike($value, 2) . "'";
			}
		}
		if ($comparator == "ew") {
			if (trim($value) == "" && ($datatype == "V" || $datatype == "E")) {
				$rtvalue = " like '" . formatForSqlLike($value, 3) . "'";
			} else {
				$rtvalue = " like '" . formatForSqlLike($value, 1) . "'";
			}
		}
		if ($comparator == "c") {
			if (trim($value) == "" && ($datatype == "V" || $datatype == "E")) {
				$rtvalue = " like '" . formatForSqlLike($value, 3) . "'";
			} else {
				$rtvalue = " like '" . formatForSqlLike($value) . "'";
			}
		}
		if ($comparator == "k") {
			if (trim($value) == "" && ($datatype == "V" || $datatype == "E")) {
				$rtvalue = " not like ''";
			} else {
				$rtvalue = " not like '" . formatForSqlLike($value) . "'";
			}
		}
		if ($comparator == "l") {
			$rtvalue = " < " . $adb->quote($value);
		}
		if ($comparator == "g") {
			$rtvalue = " > " . $adb->quote($value);
		}
		if ($comparator == "m") {
			$rtvalue = " <= " . $adb->quote($value);
		}
		if ($comparator == "h") {
			$rtvalue = " >= " . $adb->quote($value);
		}
		if ($comparator == "b") {
			$rtvalue = " < " . $adb->quote($value);
		}
		if ($comparator == "a") {
			$rtvalue = " > " . $adb->quote($value);
		}

		return $rtvalue;
	}

	/** to get the date value for the given type
	 * @param $type :: type string
	 * @returns  $datevalue array in the following format
	 *             $datevalue = Array(0=>$startdate,1=>$enddate)
	 */
	public function getDateforStdFilterBytype($type)
	{
		return DateTimeRange::getDateRangeByType($type);
	}

	/** to get the customview query for the given customview
	 * @param $viewid (custom view id):: type Integer
	 * @param $listquery (List View Query):: type string
	 * @param $module (Module Name):: type string
	 * @returns  $query
	 */
	//CHANGE : TO IMPROVE PERFORMANCE
	public function getModifiedCvListQuery($viewid, $listquery, $module)
	{
		if ($viewid != "" && $listquery != "") {

			$listviewquery = substr($listquery, strpos($listquery, 'FROM'), strlen($listquery));
			if ($module == "Calendar" || $module == "Emails") {
				$query = 'select %s, vtiger_activity.activityid, vtiger_activity.activitytype as type, vtiger_activity.priority, vtiger_activity.status as status, vtiger_crmentity.crmid,vtiger_contactdetails.contactid %s';
			} else if ($module == "Documents") {
				$query = 'select %s ,vtiger_crmentity.crmid,vtiger_notes.* %s';
			} else if ($module == "Products") {
				$query = 'select %s ,vtiger_crmentity.crmid,vtiger_products.* %s';
			} else if ($module == "Vendors") {
				$query = 'select %s ,vtiger_crmentity.crmid %s';
			} else if ($module == "PriceBooks") {
				$query = 'select %s ,vtiger_crmentity.crmid %s';
			} else if ($module == "Faq") {
				$query = 'select %s ,vtiger_crmentity.crmid %s';
			} else if ($module == "Contacts") {
				$query = 'select %s ,vtiger_crmentity.crmid,vtiger_account.accountid %s';
			} else {
				$query = 'select %s ,vtiger_crmentity.crmid %s';
			}

			$query = sprintf($query, $this->getCvColumnListSQL($viewid), $listviewquery);
			$stdfiltersql = $this->getCVStdFilterSQL($viewid);
			$advfiltersql = $this->getCVAdvFilterSQL($viewid);
			if (isset($stdfiltersql) && $stdfiltersql != '') {
				$query .= ' and ' . $stdfiltersql;
			}
			if (isset($advfiltersql) && $advfiltersql != '') {
				$query .= ' and ' . $advfiltersql;
			}
		}
		return $query;
	}

	/** to get the Key Metrics for the home page query for the given customview  to find the no of records
	 * @param $viewid (custom view id):: type Integer
	 * @param $listquery (List View Query):: type string
	 * @param $module (Module Name):: type string
	 * @returns  $query
	 */
	public function getMetricsCvListQuery($viewid, $listquery, $module)
	{
		if ($viewid != "" && $listquery != "") {
			$listviewquery = substr($listquery, strpos($listquery, 'FROM'), strlen($listquery));

			$query = 'select count(*) AS count %s';

			$stdfiltersql = $this->getCVStdFilterSQL($viewid);
			$advfiltersql = $this->getCVAdvFilterSQL($viewid);
			if (isset($stdfiltersql) && $stdfiltersql != '') {
				$query .= ' and ' . $stdfiltersql;
			}
			if (isset($advfiltersql) && $advfiltersql != '') {
				$query .= ' and ' . $advfiltersql;
			}
		}
		$query = sprintf($query, $listviewquery);
		return $query;
	}

	/** to get the custom action details for the given customview
	 * @param $viewid (custom view id):: type Integer
	 * @returns  $calist array in the following format
	 * $calist = Array ('subject'=>$subject,
	  'module'=>$module,
	  'content'=>$content,
	  'cvid'=>$custom view id)
	 */
	public function getCustomActionDetails($cvid)
	{
		$adb = PearDatabase::getInstance();

		$sSQL = "select vtiger_customaction.* from vtiger_customaction inner join vtiger_customview on vtiger_customaction.cvid = vtiger_customview.cvid";
		$sSQL .= " where vtiger_customaction.cvid=?";
		$result = $adb->pquery($sSQL, array($cvid));

		while ($carow = $adb->fetch_array($result)) {
			$calist["subject"] = $carow["subject"];
			$calist["module"] = $carow["module"];
			$calist["content"] = $carow["content"];
			$calist["cvid"] = $carow["cvid"];
		}
		return $calist;
	}
	/* This function sets the block information for the given module to the class variable module_list
	 * and return the array
	 */

	public function getCustomViewModuleInfo($module)
	{
		$adb = PearDatabase::getInstance();
		$current_language = vglobal('current_language');
		$current_mod_strings = return_specified_module_language($current_language, $module);
		$blockInfo = [];
		$modulesList = explode(',', $module);
		if ($module == 'Calendar') {
			$module = "Calendar','Events";
			$modulesList = array('Calendar', 'Events');
		}

		// Tabid mapped to the list of block labels to be skipped for that tab.
		$skipBlocksList = array(
			\includes\Modules::getModuleId('HelpDesk') => array('LBL_COMMENTS'),
			\includes\Modules::getModuleId('Faq') => array('LBL_COMMENT_INFORMATION')
		);

		$sql = sprintf('SELECT DISTINCT block,vtiger_field.tabid,`name`,blocklabel FROM	vtiger_field 
					INNER JOIN vtiger_blocks ON vtiger_blocks.blockid = vtiger_field.block INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid 
				  WHERE vtiger_tab.name IN (%s) && vtiger_field.presence IN (0, 2)', generateQuestionMarks($modulesList));
		$result = $adb->pquery($sql, [$modulesList]);
		if ($module == "Calendar','Events")
			$module = 'Calendar';

		$preBlockLabel = '';
		while ($block = $adb->getRow($result)) {
			$blockLabel = $block['blocklabel'];
			$tabid = $block['tabid'];
			// Skip certain blocks of certain modules
			if (array_key_exists($tabid, $skipBlocksList) && in_array($blockLabel, $skipBlocksList[$tabid]))
				continue;

			if (trim($blockLabel) == '') {
				$blockInfo[$preBlockLabel] = $blockInfo[$preBlockLabel] . ',' . $block['block'];
			} else {
				$lanBlockLabel = $current_mod_strings[$blockLabel];
				if (!empty($blockInfo[$lanBlockLabel])) {
					$blockInfo[$lanBlockLabel] = $blockInfo[$lanBlockLabel] . ',' . $block['block'];
				} else {
					$blockInfo[$lanBlockLabel] = $block['block'];
				}
				$preBlockLabel = $lanBlockLabel;
			}
		}
		$this->module_list[$module] = $blockInfo;
		return $this->module_list;
	}

	/**
	 * Get the userid, status information of this custom view.
	 *
	 * @param Integer $viewid
	 * @return Array
	 */
	public function getStatusAndUserid($viewid)
	{
		$adb = PearDatabase::getInstance();

		if ($this->_status === false || $this->_userid === false) {
			$query = "SELECT status, userid FROM vtiger_customview WHERE cvid=?";
			$result = $adb->pquery($query, array($viewid));
			if ($result && $adb->num_rows($result)) {
				$this->_status = $adb->query_result($result, 0, 'status');
				$this->_userid = $adb->query_result($result, 0, 'userid');
			} else {
				return false;
			}
		}
		return array('status' => $this->_status, 'userid' => $this->_userid);
	}

	//Function to check if the current user is able to see the customView
	public function isPermittedCustomView($record_id, $action, $module)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$log->debug("Entering isPermittedCustomView($record_id,$action,$module) method....");

		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		$permission = "yes";

		if ($record_id != '') {
			$status_userid_info = $this->getStatusAndUserid($record_id);

			if ($status_userid_info) {
				$status = $status_userid_info['status'];
				$userid = $status_userid_info['userid'];

				if ($status == CV_STATUS_DEFAULT) {
					$log->debug('Entering when status=0');
					$permission = 'yes';
				} elseif ($is_admin) {
					$permission = 'yes';
				} elseif ($action != 'ChangeStatus') {
					if ($userid == $current_user->id) {
						$log->debug("Entering when $userid=$current_user->id");
						$permission = 'yes';
					} elseif ($status == CV_STATUS_PUBLIC) {
						$log->debug('Entering when status=3');
						$permission = 'yes';
					} elseif ($status == CV_STATUS_PRIVATE || $status == CV_STATUS_PENDING) {
						$log->debug('Entering when status=1 or 2');
						if ($userid == $current_user->id)
							$permission = "yes";
						else {
							$log->debug("Entering when status=1 or status=2 & action = ListView or $module.Ajax or index");
							$sql = sprintf('SELECT 
										vtiger_users.id 
									  FROM
										vtiger_customview 
										INNER JOIN vtiger_users 
									  WHERE vtiger_customview.cvid = ? 
										AND vtiger_customview.userid IN 
										(SELECT 
										  vtiger_user2role.userid 
										FROM
										  vtiger_user2role 
										  INNER JOIN vtiger_users 
											ON vtiger_users.id = vtiger_user2role.userid 
										  INNER JOIN vtiger_role 
											ON vtiger_role.roleid = vtiger_user2role.roleid 
										WHERE vtiger_role.parentrole LIKE %s)', "'%$current_user_parent_role_seq::%'");
							$result = $adb->pquery($sql, [$record_id]);

							while ($row = $adb->fetchByAssoc($result)) {
								$temp_result[] = $row['id'];
							}
							$user_array = $temp_result;
							if (sizeof($user_array) > 0) {
								if (!in_array($current_user->id, $user_array))
									$permission = 'no';
								else
									$permission = 'yes';
							} else
								$permission = 'no';
						}
					} else
						$permission = 'yes';
				}
				else {
					$log->debug('Entering else condition............');
					$permission = 'no';
				}
			} else {
				$log->debug('Enters when count =0');
				$permission = 'no';
			}
		}
		$log->debug("Permission @@@@@@@@@@@@@@@@@@@@@@@@@@@ : $permission");
		$log->debug("Exiting isPermittedCustomView($record_id,$action,$module) method....");
		return $permission;
	}

	public function isPermittedChangeStatus($status)
	{
		global $current_user, $log;
		$current_language = vglobal('current_language');
		$custom_strings = return_module_language($current_language, "CustomView");

		$log->debug("Entering isPermittedChangeStatus($status) method..............");
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		$status_details = [];
		if ($is_admin) {
			if ($status == CV_STATUS_PENDING) {
				$changed_status = CV_STATUS_PUBLIC;
				$status_label = $custom_strings['LBL_STATUS_PUBLIC_APPROVE'];
			} elseif ($status == CV_STATUS_PUBLIC) {
				$changed_status = CV_STATUS_PENDING;
				$status_label = $custom_strings['LBL_STATUS_PUBLIC_DENY'];
			}
			$status_details = Array('Status' => $status, 'ChangedStatus' => $changed_status, 'Label' => $status_label);
		}
		$log->debug("Exiting isPermittedChangeStatus($status) method..............");
		return $status_details;
	}
}

?>
