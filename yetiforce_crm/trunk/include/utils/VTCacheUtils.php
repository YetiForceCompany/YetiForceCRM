<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * Class to handle Caching Mechanism and re-use information.
 */
require_once 'includes/runtime/Cache.php';
class VTCacheUtils {

	/** Tab information caching */
	static $_tabidinfo_cache = array();
	static function lookupTabid($module) {
		$flip_cache = array_flip(self::$_tabidinfo_cache);

		if(isset($flip_cache[$module])) {
			return $flip_cache[$module];
		}
		return false;
	}

	static function lookupModulename($tabid) {
		if(isset(self::$_tabidinfo_cache[$tabid])) {
			return self::$_tabidinfo_cache[$tabid];
		}
		return false;
	}

	static function updateTabidInfo($tabid, $module) {
		if(!empty($tabid) && !empty($module)) {
			self::$_tabidinfo_cache[$tabid] = $module;
		}
	}

	/** All tab information caching */
	static $_alltabrows_cache = false;
	static function lookupAllTabsInfo() {
		return self::$_alltabrows_cache;
	}
	static function updateAllTabsInfo($tabrows) {
		self::$_alltabrows_cache = $tabrows;
	}

	/** Block information caching */
	static $_blocklabel_cache = array();
	static function updateBlockLabelWithId($label, $id) {
		self::$_blocklabel_cache[$id] = $label;
	}
	static function lookupBlockLabelWithId($id) {
		if (isset(self::$_blocklabel_cache[$id])) {
			return self::$_blocklabel_cache[$id];
		}
		return false;
	}

	/** Field information caching */
	static $_fieldinfo_cache = array();
	static function updateFieldInfo($tabid, $fieldname, $fieldid, $fieldlabel,
		$columnname, $tablename, $uitype, $typeofdata, $presence) {

		self::$_fieldinfo_cache[$tabid][$fieldname] = array(
			'tabid'     => $tabid,
			'fieldid'   => $fieldid,
			'fieldname' => $fieldname,
			'fieldlabel'=> $fieldlabel,
			'columnname'=> $columnname,
			'tablename' => $tablename,
			'uitype'    => $uitype,
			'typeofdata'=> $typeofdata,
			'presence'  => $presence,
		);
        Vtiger_Cache::set('fieldInfo', $tabid, self::$_fieldinfo_cache[$tabid]);
	}
	static function lookupFieldInfo($tabid, $fieldname) {
        $fieldInfo = Vtiger_Cache::get('fieldInfo', $tabid);
        if($fieldInfo && isset($fieldInfo[$fieldname])){
            return $fieldInfo[$fieldname];
        }else if(isset(self::$_fieldinfo_cache[$tabid]) && isset(self::$_fieldinfo_cache[$tabid][$fieldname])) {
			return self::$_fieldinfo_cache[$tabid][$fieldname];
		}

        $field = Vtiger_Cache::get('field-'.$tabid,$fieldname);
        if($field){
            $cacheField = array(
                'tabid' => $tabid,
                'fieldid' => $field->getId(),
                'fieldname' => $field->getName(),
                'fieldlabel' => $field->get('label'),
                'columnname' => $field->get('column'),
                'tablename' => $field->get('table'),
                'uitype' => $field->get('uitype'),
                'typeofdata' => $field->get('typeofdata'),
                'presence' => $field->get('presence'),
            );
            return $cacheField;
        }
		return false;
	}
	static function lookupFieldInfo_Module($module, $presencein = array('0', '2')) {
		$tabid = getTabid($module);
		$modulefields = false;
		$fieldInfo = Vtiger_Cache::get('fieldInfo', $tabid);
        if($fieldInfo){
            $fldcache =$fieldInfo;
        }else if(isset(self::$_fieldinfo_cache[$tabid])) {
            $fldcache = self::$_fieldinfo_cache[$tabid];
        }

        if($fldcache){
            $modulefields = array();

			foreach($fldcache as $fieldname=>$fieldinfo) {
				if(in_array($fieldinfo['presence'], $presencein)) {
					$modulefields[] = $fieldinfo;
				}
			}
		}

        $fieldInfo = Vtiger_Cache::get('ModuleFields',$tabid);
        if($fieldInfo){
            foreach($fieldInfo as $block => $blockFields){
                foreach ($blockFields as $field){
                if(in_array($field->get('presence'), $presencein)) {
                     $cacheField = array(
                            'tabid' => $tabid,
                            'fieldid' => $field->getId(),
                            'fieldname' => $field->getName(),
                            'fieldlabel' => $field->get('label'),
                            'columnname' => $field->get('column'),
                            'tablename' => $field->get('table'),
                            'uitype' => $field->get('uitype'),
                            'typeofdata' => $field->get('typeofdata'),
                            'presence' => $field->get('presence'),
                        );
                     $modulefields[] = $cacheField;
                 }
                }
            }
        }
		return $modulefields;
	}

	static function lookupFieldInfoByColumn($tabid, $columnname) {

        if(isset(self::$_fieldinfo_cache[$tabid])) {
			foreach(self::$_fieldinfo_cache[$tabid] as $fieldname=>$fieldinfo) {
				if($fieldinfo['columnname'] == $columnname) {
					return $fieldinfo;
				}
			}
		}

        $fieldInfo = Vtiger_Cache::get('ModuleFields',$tabid);
        if($fieldInfo){
            foreach($fieldInfo as $block => $blockFields){
                foreach ($blockFields as $field){
                 if($field->get('column') == $columnname) {
                     $cacheField = array(
                            'tabid' => $tabid,
                            'fieldid' => $field->getId(),
                            'fieldname' => $field->getName(),
                            'fieldlabel' => $field->get('label'),
                            'columnname' => $field->get('column'),
                            'tablename' => $field->get('table'),
                            'uitype' => $field->get('uitype'),
                            'typeofdata' => $field->get('typeofdata'),
                            'presence' => $field->get('presence'),
                        );
                        return $cacheField;
                 }
                }
            }
        }
		return false;
	}

	/** Entityname information */
	static $_module_entityname_cache = array();
	static function updateEntityNameInfo($module, $data) {
		self::$_module_entityname_cache[$module] = $data;
        Vtiger_Cache::set('EntityInfo', $module, self::$_module_entityname_cache[$module]);
	}
	static function lookupEntityNameInfo($module) {
        $entityNames = Vtiger_Cache::get('EntityInfo', $module);
        if($entityNames){
            return $entityNames;
        }else if (isset(self::$_module_entityname_cache[$module])) {
			return self::$_module_entityname_cache[$module];
		}
		return false;
	}

	/** Module active column fields caching */
	static $_module_columnfields_cache = array();
	static function updateModuleColumnFields($module, $column_fields) {
		self::$_module_columnfields_cache[$module] = $column_fields;
	}
	static function lookupModuleColumnFields($module) {
		if(isset(self::$_module_columnfields_cache[$module])) {
			return self::$_module_columnfields_cache[$module];
		}
		return false;
	}

	/** User currency id caching */
	static $_usercurrencyid_cache = array();
	static function lookupUserCurrenyId($userid) {
		global $current_user;
		if(isset($current_user) && $current_user->id == $userid) {
			return array(
				'currencyid' => $current_user->column_fields['currency_id']
			);
		}

		if(isset(self::$_usercurrencyid_cache[$userid])) {
			return self::$_usercurrencyid_cache[$userid];
		}

		return false;
	}
	static function updateUserCurrencyId($userid, $currencyid) {
		self::$_usercurrencyid_cache[$userid] = array(
			'currencyid' => $currencyid
		);
	}

	/** Currency information caching */
	static $_currencyinfo_cache = array();
	static function lookupCurrencyInfo($currencyid) {
		if(isset(self::$_currencyinfo_cache[$currencyid])) {
			return self::$_currencyinfo_cache[$currencyid];
		}
		return false;
	}
	static function updateCurrencyInfo($currencyid, $name, $code, $symbol, $rate) {
		self::$_currencyinfo_cache[$currencyid] = array(
			'currencyid' => $currencyid,
			'name'       => $name,
			'code'       => $code,
			'symbol'     => $symbol,
			'rate'       => $rate
		);
	}


	/** ProfileId information caching */
	static $_userprofileid_cache = array();
	static function updateUserProfileId($userid, $profileid) {
		self::$_userprofileid_cache[$userid] = $profileid;
	}
	static function lookupUserProfileId($userid) {
		if(isset(self::$_userprofileid_cache[$userid])) {
			return self::$_userprofileid_cache[$userid];
		}
		return false;
	}

	/** Profile2Field information caching */
	static $_profile2fieldpermissionlist_cache = array();
	static function lookupProfile2FieldPermissionList($module, $profileid) {
		$pro2fld_perm = self::$_profile2fieldpermissionlist_cache;
		if(isset($pro2fld_perm[$module]) && isset($pro2fld_perm[$module][$profileid])) {
			return $pro2fld_perm[$module][$profileid];
		}
		return false;
	}
	static function updateProfile2FieldPermissionList($module, $profileid, $value) {
		self::$_profile2fieldpermissionlist_cache[$module][$profileid] = $value;
	}

	/** Role information */
	static $_subroles_roleid_cache = array();
	static function lookupRoleSubordinates($roleid) {
		if(isset(self::$_subroles_roleid_cache[$roleid])) {
			return self::$_subroles_roleid_cache[$roleid];
		}
		return false;
	}
	static function updateRoleSubordinates($roleid, $roles) {
		self::$_subroles_roleid_cache[$roleid] = $roles;
	}
	static function clearRoleSubordinates($roleid = false) {
		if($roleid === false) {
			self::$_subroles_roleid_cache = array();
		} else if(isset(self::$_subroles_roleid_cache[$roleid])) {
			unset(self::$_subroles_roleid_cache[$roleid]);
		}
	}
	
	/** Record Owner Id */
	static $_record_ownerid_cache = array();
	static function lookupRecordOwner($record) {
		if(isset(self::$_record_ownerid_cache[$record])) {
			return self::$_record_ownerid_cache[$record];
		}
		return false;
	}
	
	static function updateRecordOwner($record, $ownerId) {
		self::$_record_ownerid_cache[$record] = $ownerId;
	}
	
	
	/** Record Owner Type */
	static $_record_ownertype_cache = array();
	static function lookupOwnerType($ownerId) {
		if(isset(self::$_record_ownertype_cache[$ownerId])) {
			return self::$_record_ownertype_cache[$ownerId];
		}
		return false;
	}
	
	static function updateOwnerType($ownerId, $count) {
		self::$_record_ownertype_cache[$ownerId] = $count;
	}
	
	/** Related module information for Report */
	static $_report_listofmodules_cache = false;
	static function lookupReport_ListofModuleInfos() {
		return self::$_report_listofmodules_cache;
	}
	static function updateReport_ListofModuleInfos($module_list, $related_modules) {
		if(self::$_report_listofmodules_cache === false) {
			self::$_report_listofmodules_cache = array(
				'module_list' => $module_list,
				'related_modules' => $related_modules
			);
		}
	}

	/** Report module information based on used. */
	static $_reportmodule_infoperuser_cache = array();
	static function lookupReport_Info($userid, $reportid) {

		if(isset(self::$_reportmodule_infoperuser_cache[$userid])) {
			if(isset(self::$_reportmodule_infoperuser_cache[$userid][$reportid])) {
				return self::$_reportmodule_infoperuser_cache[$userid][$reportid];
			}
		}
		return false;
	}
	static function updateReport_Info($userid, $reportid, $primarymodule, $secondarymodules, $reporttype,
	$reportname, $description, $folderid, $owner) {
		if(!isset(self::$_reportmodule_infoperuser_cache[$userid])) {
			self::$_reportmodule_infoperuser_cache[$userid] = array();
		}
		if(!isset(self::$_reportmodule_infoperuser_cache[$userid][$reportid])) {
			self::$_reportmodule_infoperuser_cache[$userid][$reportid] = array (
				'reportid'        => $reportid,
				'primarymodule'   => $primarymodule,
				'secondarymodules'=> $secondarymodules,
				'reporttype'      => $reporttype,
				'reportname'      => $reportname,
				'description'     => $description,
				'folderid'        => $folderid,
				'owner'           => $owner
			);
		}
	}

	/** Report module sub-ordinate users information. */
	static $_reportmodule_subordinateuserid_cache = array();
	static function lookupReport_SubordinateUsers($reportid) {
		if(isset(self::$_reportmodule_subordinateuserid_cache[$reportid])) {
			return self::$_reportmodule_subordinateuserid_cache[$reportid];
		}
		return false;
	}
	static function updateReport_SubordinateUsers($reportid, $userids) {
		self::$_reportmodule_subordinateuserid_cache[$reportid] = $userids;
	}

	/** Report module information based on used. */
	static $_reportmodule_scheduledinfoperuser_cache = array();
	static function lookupReport_ScheduledInfo($userid, $reportid) {

		if(isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid])) {
			if(isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid])) {
				return self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid];
			}
		}
		return false;
	}
	static function updateReport_ScheduledInfo($userid, $reportid, $isScheduled, $scheduledFormat, $scheduledInterval, $scheduledRecipients, $scheduledTime) {
		if(!isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid])) {
			self::$_reportmodule_scheduledinfoperuser_cache[$userid] = array();
		}
		if(!isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid])) {
			self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid] = array (
				'reportid'				=> $reportid,
				'isScheduled'			=> $isScheduled,
				'scheduledFormat'		=> $scheduledFormat,
				'scheduledInterval'		=> $scheduledInterval,
				'scheduledRecipients'	=> $scheduledRecipients,
				'scheduledTime'			=> $scheduledTime,
			);
		}
	}

    static $_outgoingMailFromEmailAddress;
    public static function setOutgoingMailFromEmailAddress($email) {
        self::$_outgoingMailFromEmailAddress = $email;
    }
    public static function getOutgoingMailFromEmailAddress() {
        return self::$_outgoingMailFromEmailAddress;
    }

    static $_userSignature = array();
    public static function setUserSignature($userName, $signature) {
        self::$_userSignature[$userName] = $signature;
    }
    public static function getUserSignature($userName) {
        return self::$_userSignature[$userName];
    }

    static $_userFullName = array();
    public static function setUserFullName($userName, $fullName) {
        self::$_userFullName[$userName] = $fullName;
    }
    public static function getUserFullName($userName) {
        return self::$_userFullName[$userName];
    }

	static $_report_field_bylabel = array();
	public static function getReportFieldByLabel($module, $label) {
		return self::$_report_field_bylabel[$module][$label];
	}

	public static function setReportFieldByLabel($module, $label, $fieldInfo) {
		self::$_report_field_bylabel[$module][$label] = $fieldInfo;
	}
}

?>