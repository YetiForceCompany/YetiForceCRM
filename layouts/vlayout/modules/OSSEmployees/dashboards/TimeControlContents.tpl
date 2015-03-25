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
<div style='padding:5px;'>
	{if $CHARTEXIST}
		<input class="widgetData" type="hidden" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATA))}' />
		<div class="widgetChartContainer" style="height:65%;width:98%"></div>
		<div class="legend-colors">
			<ol>
			{foreach from=$TIMETYPESCOLORS key=TIMETYPE item=COLOR}
				<li style="float:left; margin-right:15px;">
					<div style="margin-right:5px; margin-top:5px; float:left; width:10px; height:10px; background-color:{$COLOR}"></div>
					<h5  style="float:left;">{vtranslate($TIMETYPE, $MODULE_NAME)} </h5>
				</li>
			{/foreach}
		</ol>
		</div>
	{else}
		<span class="noDataMsg">
			{vtranslate('LBL_NO_DATA', $MODULE_NAME)}
		</span>
	{/if}
	<div class="">		
		<select name="timeTypes" class="widgetFilter chzn-select" multiple style="width:80%; height: 100px;">
			{foreach key=KEY item=ITEM from=$TIMETYPEPOSSIBILITY}
				{if $SELECTEDTIMETYPES eq 'all'}
					<option selected value="{$ITEM}">
				{elseif in_array($ITEM, $SELECTEDTIMETYPES)}
					<option selected value="{$ITEM}">
				{else}
					<option  value="{$ITEM}">
				{/if}
					{vtranslate($KEY, $MODULE_NAME)}
				</option>
			{/foreach}
		</select>
	</div>
</div>
{/strip}