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

	/** to get the custom action details for the given customview
	 * @param $cvid (custom view id):: type Integer
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
