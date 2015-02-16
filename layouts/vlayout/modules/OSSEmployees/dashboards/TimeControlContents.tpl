{*<!--
/*********************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ********************************************************************************/
-->*}
{strip}
{if count($DATA) gt 0 }
	<input class="widgetData" type="hidden" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATA))}' />
	<div class="widgetChartContainer" style="height:65%;width:98%"></div>
	<div class="widgetDescContainer" style="margin-left: 10px;margin-top: 10px">
		<h5>{vtranslate('LBL_TIME_RANGE', $MODULE_NAME)}: {$DTIME['start']} - {$DTIME['end']}</h5>
		<h5>{vtranslate('LBL_NUMBER_OF_DAYS', $MODULE_NAME)}: {$SELECTEDDAYS}</h5>
		<h5>{vtranslate('LBL_NUMBER_OF_WORKING_DAYS', $MODULE_NAME)}: {$WORKDAYS}</h5>
		<h5>{vtranslate('LBL_NUMBER_OF_DAYS_WORKED', $MODULE_NAME)}: {$COUNTDAYS}</h5>
		<h5>{vtranslate('LBL_AVERAGE_WORKING_TIME', $MODULE_NAME)}: {$AVERAGE}</h5>
	</div>
{else}
	<span class="noDataMsg">
		{vtranslate('LBL_NO_DATA', $MODULE_NAME)}
	</span>
{/if}
{/strip}