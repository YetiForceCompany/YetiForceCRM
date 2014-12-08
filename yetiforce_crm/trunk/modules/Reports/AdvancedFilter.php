<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
require_once('include/Zend/Json.php');
if(isset($_REQUEST["record"]) && $_REQUEST['record']!='')
{
	$reportid = vtlib_purify($_REQUEST["record"]);
	$oReport = new Reports($reportid);
	$oReport->getAdvancedFilterList($reportid);

	$oRep = new Reports();
	$secondarymodule = '';
	$secondarymodules =Array();
	
	if(!empty($oRep->related_modules[$oReport->primodule])) {
		foreach($oRep->related_modules[$oReport->primodule] as $key=>$value){
			if(isset($_REQUEST["secondarymodule_".$value]))$secondarymodules []= $_REQUEST["secondarymodule_".$value];
		}
	}
	$secondarymodule = implode(":",$secondarymodules);
	
	if($secondarymodule!='')
		$oReport->secmodule = $secondarymodule;
	
	$COLUMNS_BLOCK = getPrimaryColumns_AdvFilterHTML($oReport->primodule);
	$COLUMNS_BLOCK .= getSecondaryColumns_AdvFilterHTML($oReport->secmodule);
	$report_std_filter->assign("COLUMNS_BLOCK", $COLUMNS_BLOCK);
	
	$FILTER_OPTION = Reports::getAdvCriteriaHTML();
	$report_std_filter->assign("FOPTION",$FILTER_OPTION);

	$rel_fields = getRelatedFieldColumns();
	$report_std_filter->assign("REL_FIELDS",Zend_Json::encode($rel_fields));
	
	$report_std_filter->assign("CRITERIA_GROUPS",$oReport->advft_criteria);
} else {
	$primarymodule = $_REQUEST["primarymodule"];
	$COLUMNS_BLOCK = getPrimaryColumns_AdvFilterHTML($primarymodule);
	$ogReport =  new Reports();
	if(!empty($ogReport->related_modules[$primarymodule])) {
		foreach($ogReport->related_modules[$primarymodule] as $key=>$value){
			//$BLOCK1 .= getSecondaryColumnsHTML($_REQUEST["secondarymodule_".$value]);
			$COLUMNS_BLOCK .= getSecondaryColumns_AdvFilterHTML($_REQUEST["secondarymodule_".$value]);
		}
	}
	$report_std_filter->assign("COLUMNS_BLOCK", $COLUMNS_BLOCK);
	
	$rel_fields = getRelatedFieldColumns();	
	$report_std_filter->assign("REL_FIELDS",Zend_Json::encode($rel_fields));
}

/** Function to get primary columns for an advanced filter
 *  This function accepts The module as an argument
 *  This generate columns of the primary modules for the advanced filter 
 *  It returns a HTML string of combo values 
 */

function getPrimaryColumns_AdvFilterHTML($module,$selected="")
{
    global $ogReport, $app_list_strings, $current_language;
	$mod_strings = return_module_language($current_language,$module);
	$block_listed = array();
    foreach($ogReport->module_list[$module] as $key=>$value)
    {
    	if(isset($ogReport->pri_module_columnslist[$module][$value]) && !$block_listed[$value])
    	{
			$block_listed[$value] = true;
			$shtml .= "<optgroup label=\"".$app_list_strings['moduleList'][$module]." ".getTranslatedString($value)."\" class=\"select\" style=\"border:none\">";
			foreach($ogReport->pri_module_columnslist[$module][$value] as $field=>$fieldlabel)
			{
				if(isset($mod_strings[$fieldlabel]))
				{
					//fix for ticket 5191
					$selected = decode_html($selected);
					$field = decode_html($field);
					//fix ends
					if($selected == $field)
					{
						$shtml .= "<option selected value=\"".$field."\">".$mod_strings[$fieldlabel]."</option>";
					}else
					{
						$shtml .= "<option value=\"".$field."\">".$mod_strings[$fieldlabel]."</option>";
					}
				}else
				{
					if($selected == $field)
					{
						$shtml .= "<option selected value=\"".$field."\">".$fieldlabel."</option>";
					}else
					{
						$shtml .= "<option value=\"".$field."\">".$fieldlabel."</option>";
					}
				}
			}
       }
    }
    return $shtml;
}



/** Function to get Secondary columns for an advanced filter
 *  This function accepts The module as an argument
 *  This generate columns of the secondary module for the advanced filter 
 *  It returns a HTML string of combo values
 */

function getSecondaryColumns_AdvFilterHTML($module,$selected="")
{
    global $ogReport;
	global $app_list_strings;
    global $current_language;

    if($module != "")
    {
    	$secmodule = explode(":",$module);
    	for($i=0;$i < count($secmodule) ;$i++)
    	{
            $mod_strings = return_module_language($current_language,$secmodule[$i]);
            if(vtlib_isModuleActive($secmodule[$i])){
				$block_listed = array();
				foreach($ogReport->module_list[$secmodule[$i]] as $key=>$value)
                {
					if(isset($ogReport->sec_module_columnslist[$secmodule[$i]][$value]) && !$block_listed[$value])
					{
						$block_listed[$value] = true;
                		  $shtml .= "<optgroup label=\"".$app_list_strings['moduleList'][$secmodule[$i]]." ".getTranslatedString($value)."\" class=\"select\" style=\"border:none\">";
						  foreach($ogReport->sec_module_columnslist[$secmodule[$i]][$value] as $field=>$fieldlabel)
						  {
							if(isset($mod_strings[$fieldlabel]))
							{
								if($selected == $field)
								{
									$shtml .= "<option selected value=\"".$field."\">".$mod_strings[$fieldlabel]."</option>";
								}else
								{
									$shtml .= "<option value=\"".$field."\">".$mod_strings[$fieldlabel]."</option>";
								}
							}else
							{
								if($selected == $field)
								{
									$shtml .= "<option selected value=\"".$field."\">".$fieldlabel."</option>";
								}else
								{
									$shtml .= "<option value=\"".$field."\">".$fieldlabel."</option>";
								}
							}
						  }
					}
                }
            }
    	}
    }
    return $shtml;
}

function getRelatedColumns($selected=""){
	global $ogReport;
	$rel_fields = $ogReport->adv_rel_fields;
	if($selected!='All'){
		$selected = split(":",$selected);
	}
	$related_fields = array();
	foreach($rel_fields as $i=>$index){
		$shtml='';
		foreach($index as $key=>$value){
			$fieldarray = split("::",$value);
			$shtml .= "<option value=\"".$fieldarray[0]."\">".$fieldarray[1]."</option>";
		}
		$related_fields[$i] = $shtml;
	}
	if(!empty($selected) && $selected[4]!='')
		return $related_fields[$selected[4]];
	else if($selected=='All'){
		return $related_fields;
	}
	else
		return ;	
}

function getRelatedFieldColumns($selected=""){
	global $ogReport;
	$rel_fields = $ogReport->adv_rel_fields;
	return $rel_fields;
}

?>