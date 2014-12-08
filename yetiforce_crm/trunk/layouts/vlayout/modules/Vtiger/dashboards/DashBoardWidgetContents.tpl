{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
{if count($DATA) gt 0 }
	<input class="widgetData" type=hidden value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATA))}' />
	<div class="widgetChartContainer" style="height:250px;width:85%"></div>
{else}
	<span class="noDataMsg">
		{vtranslate('LBL_NO')} {vtranslate($MODULE_NAME, $MODULE_NAME)} {vtranslate('LBL_MATCHED_THIS_CRITERIA')}
	</span>
{/if}
{/strip}