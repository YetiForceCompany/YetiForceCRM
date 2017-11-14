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

class CustomView extends CRMEntity
{

	public $module_list = [];
	public $customviewmodule;
	public $list_fields;
	public $list_fields_name;
	public $setdefaultviewid;
	public $escapemodule;
	public $mandatoryvalues;
	public $showvalues;
	public $data_type;
	// Information as defined for this instance in the database table.
	protected $_status = false;
	protected $_userid = false;

	/** This function sets the currentuser id to the class variable smownerid,
	 * modulename to the class variable customviewmodule
	 * @param $module -- The module Name:: Type String(optional)
	 * @returns  nothing
	 */
	public function __construct($module = '')
	{
		$currentUser = vglobal('current_user');
		$this->customviewmodule = $module;
		$this->escapemodule[] = $module . '_';
		$this->escapemodule[] = '_';
		$this->smownerid = $currentUser->id;
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
		$tabid = \App\Module::getModuleId($this->customviewmodule);

		require('user_privileges/user_privileges_' . $current_user->id . '.php');

		$ssql = "select vtiger_customview.* from vtiger_customview inner join vtiger_tab on vtiger_tab.name = vtiger_customview.entitytype";
		$ssql .= " where vtiger_customview.cvid=?";
		$sparams = [$cvid];

		if ($is_admin === false) {
			$ssql .= " and (vtiger_customview.status=0 or vtiger_customview.userid = ? or vtiger_customview.status = 3 or vtiger_customview.userid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'))";
			array_push($sparams, $current_user->id);
		}
		$result = $adb->pquery($ssql, $sparams);

		$usercv_result = $adb->pquery("select default_cvid from vtiger_user_module_preferences where userid = ? and tabid = ?", [$current_user->id, $tabid]);
		$def_cvid = $adb->queryResult($usercv_result, 0, 'default_cvid');

		while ($cvrow = $adb->fetchArray($result)) {
			$customviewlist['viewname'] = $cvrow['viewname'];
			if ((isset($def_cvid) || $def_cvid != '') && $def_cvid == $cvid) {
				$customviewlist['setdefault'] = 1;
			} else {
				$customviewlist['setdefault'] = $cvrow['setdefault'];
			}
			$customviewlist['setmetrics'] = $cvrow['setmetrics'];
			$customviewlist['userid'] = $cvrow['userid'];
			$customviewlist['status'] = $cvrow['status'];
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
		$tabid = \App\Module::getModuleId($this->customviewmodule);

		require('user_privileges/user_privileges_' . $current_user->id . '.php');

		$shtml_user = '';
		$shtml_pending = '';
		$shtml_public = '';
		$shtml_others = '';

		$selected = 'selected';
		if ($markselected === false)
			$selected = '';

		$ssql = "select vtiger_customview.*, vtiger_users.first_name,vtiger_users.last_name from vtiger_customview inner join vtiger_tab on vtiger_tab.name = vtiger_customview.entitytype
					left join vtiger_users on vtiger_customview.userid = vtiger_users.id ";
		$ssql .= " where vtiger_tab.tabid=?";
		$sparams = [$tabid];

		if ($is_admin === false) {
			$ssql .= " and (vtiger_customview.status=0 or vtiger_customview.userid = ? or vtiger_customview.status = 3 or vtiger_customview.userid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'))";
			array_push($sparams, $current_user->id);
		}
		$ssql .= " ORDER BY viewname";
		$result = $adb->pquery($ssql, $sparams);
		while ($cvrow = $adb->fetchArray($result)) {
			if ($cvrow['viewname'] == 'All') {
				$cvrow['viewname'] = \App\Language::translate('COMBO_ALL');
			}

			$option = '';
			$viewname = $cvrow['viewname'];
			if ($cvrow['status'] == App\CustomView::CV_STATUS_DEFAULT || $cvrow['userid'] == $current_user->id) {
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
				if ($cvrow['status'] == App\CustomView::CV_STATUS_DEFAULT || $cvrow['userid'] == $current_user->id) {
					$shtml_user .= $option;
				} elseif ($cvrow['status'] == App\CustomView::CV_STATUS_PUBLIC) {
					if ($shtml_public == '')
						$shtml_public = "<option disabled>--- " . \App\Language::translate('LBL_PUBLIC') . " ---</option>";
					$shtml_public .= $option;
				} elseif ($cvrow['status'] == App\CustomView::CV_STATUS_PENDING) {
					if ($shtml_pending == '')
						$shtml_pending = "<option disabled>--- " . \App\Language::translate('LBL_PENDING') . " ---</option>";
					$shtml_pending .= $option;
				} else {
					if ($shtml_others == '')
						$shtml_others = "<option disabled>--- " . \App\Language::translate('LBL_OTHERS') . " ---</option>";
					$shtml_others .= $option;
				}
			}
		}
		$shtml = $shtml_user;
		if ($is_admin === true)
			$shtml .= $shtml_pending;
		$shtml = $shtml . $shtml_public . $shtml_others;
		return $shtml;
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

		\App\Log::trace('Entering ' . __METHOD__ . ' method ...');

		$sSQL = 'select vtiger_cvcolumnlist.* from vtiger_cvcolumnlist';
		$sSQL .= ' inner join vtiger_customview on vtiger_customview.cvid = vtiger_cvcolumnlist.cvid';
		$sSQL .= ' where vtiger_customview.cvid =? order by vtiger_cvcolumnlist.columnindex';
		$result = $adb->pquery($sSQL, [$cvid]);

		if ($adb->numRows($result) == 0 && is_numeric($cvid) && $this->customviewmodule != 'Users') {
			\App\Log::trace("Error !!!: " . \App\Language::translate('LBL_NO_FOUND_VIEW') . " ID: $cvid");
			throw new \App\Exceptions\AppException('LBL_NO_FOUND_VIEW');
		} else if (!is_numeric($cvid) && $this->customviewmodule != 'Users') {
			$filterDir = 'modules' . DIRECTORY_SEPARATOR . $this->customviewmodule . DIRECTORY_SEPARATOR . 'filters' . DIRECTORY_SEPARATOR . $cvid . '.php';
			if (file_exists($filterDir)) {
				$handlerClass = Vtiger_Loader::getComponentClassName('Filter', $cvid, $this->customviewmodule);
				if (class_exists($handlerClass)) {
					$handler = new $handlerClass();
					$columnlist = $handler->getColumnList();
				}
			} else {
				\App\Log::trace("Error !!!: " . \App\Language::translate('LBL_NO_FOUND_VIEW') . " Filter: $cvid");
				throw new \App\Exceptions\AppException('LBL_NO_FOUND_VIEW');
			}
		} else {
			while ($columnrow = $adb->fetchArray($result)) {
				$columnlist[$columnrow['columnindex']] = $columnrow['columnname'];
			}
		}
		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');
		return $columnlist;
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
			$stdfilterrow = (new \App\Db\Query())->select('vtiger_cvstdfilter.*')
				->from('vtiger_cvstdfilter')
				->innerJoin('vtiger_customview', 'vtiger_cvstdfilter.cvid = vtiger_customview.cvid')
				->where(['vtiger_cvstdfilter.cvid' => $cvid])
				->one();
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
		$stdFilter = \App\CustomView::resolveDateFilterValue($stdfilterrow);
		Vtiger_Cache::set('getStdFilterByCvid', $cvid, $stdFilter);
		return $stdFilter;
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
	 * @param string $columnName
	 * @param string $tableName
	 */
	public function isFieldPresentByColumnTable($columnName, $tableName)
	{
		if (!isset($this->_fieldby_tblcol_cache[$tableName])) {
			$rows = (new App\Db\Query())->select(['columnname'])->from('vtiger_field')->where(['tablename' => $tableName, 'presence' => [0, 2]])->column();
			if ($rows) {
				$this->_fieldby_tblcol_cache[$tableName] = $rows;
			}
		}
		// If still the field was not found (might be disabled or deleted?)
		if (!isset($this->_fieldby_tblcol_cache[$tableName])) {
			return false;
		}
		return in_array($columnName, $this->_fieldby_tblcol_cache[$tableName]);
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
		$columnsList = $this->getColumnsListByCvid($cvid);
		if (isset($columnsList)) {
			foreach ($columnsList as $columnName => $value) {
				$tableField = '';
				if ($value !== '') {
					$list = explode(':', $value);

					//Added For getting status for Activities -Jaguar
					$sqlListColumn = $list[0] . '.' . $list[1];
					if ($this->customviewmodule === 'Calendar') {
						if ($list[1] === 'status' || $list[1] === 'activitystatus') {
							$sqlListColumn = 'vtiger_activity.status as activitystatus';
						}
					}
					//Added for assigned to sorting
					if ($list[1] === 'smownerid') {
						$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' =>
								'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
						$sqlListColumn = "case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name";
					}
					if ($list[0] === 'vtiger_contactdetails' && $list[1] === 'lastname')
						$sqlListColumn = 'vtiger_contactdetails.lastname,vtiger_contactdetails.firstname';
					$sqlList[] = $sqlListColumn;
					//Ends

					$tableField[$list[0]] = $list[1];

					//Changed as the replace of module name may replace the string if the fieldname has module name in it -- Jeri
					$fieldInfo = explode('_', $list[3], 2);
					$fieldLabel = $fieldInfo[1];
					$fieldLabel = str_replace('_', ' ', $fieldLabel);

					if ($this->isFieldPresentByColumnTable($list[1], $list[0])) {

						$this->list_fields[$fieldLabel] = $tableField;
						$this->list_fields_name[$fieldLabel] = $list[2];
					}
				}
			}
			$returnsql = implode(',', $sqlList);
		}
		return $returnsql;
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
		return (new App\Db\Query())->select(['subject' => 'vtiger_customaction.subject', 'module' => 'vtiger_customaction.module', 'content' => 'vtiger_customaction.content', 'cvid' => 'vtiger_customaction.cvid'])->from('vtiger_customaction')->innerJoin('vtiger_customview', 'vtiger_customaction.cvid = vtiger_customview.cvid')->where(['vtiger_customaction.cvid' => $cvid])->one();
	}

	public function isPermittedChangeStatus($status)
	{
		$currentLanguage = vglobal('current_language');
		$currentUser = vglobal('current_user');
		$custom_strings = \vtlib\Deprecated::getModuleTranslationStrings($currentLanguage, "CustomView");

		\App\Log::trace("Entering isPermittedChangeStatus($status) method..............");
		require('user_privileges/user_privileges_' . $currentUser->id . '.php');
		$status_details = [];
		if ($is_admin) {
			if ($status == App\CustomView::CV_STATUS_PENDING) {
				$changed_status = App\CustomView::CV_STATUS_PUBLIC;
				$status_label = $custom_strings['LBL_STATUS_PUBLIC_APPROVE'];
			} elseif ($status == App\CustomView::CV_STATUS_PUBLIC) {
				$changed_status = App\CustomView::CV_STATUS_PENDING;
				$status_label = $custom_strings['LBL_STATUS_PUBLIC_DENY'];
			}
			$status_details = ['Status' => $status, 'ChangedStatus' => $changed_status, 'Label' => $status_label];
		}
		\App\Log::trace("Exiting isPermittedChangeStatus($status) method..............");
		return $status_details;
	}
}
