{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
{strip}
<div class='widget_header row '>
	<div class="col-xs-12">
		{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
	</div>
</div>
<div class="widget_contents">
							</div>
<table class="table" style="width:100%;">
	<tbody>
			<tr class="summaryViewEntries">
				<td class="fieldLabel" style="padding-bottom:0px; padding-top:12px" ><label class="muted">{vtranslate('LBL_YEAR',$MODULE_NAME)}</label></td>
				<td class="fieldLabel" style="padding-bottom:0px">
                    <span class="">
						<select name="year">
						{foreach item=item from=$YEARS}
						<option {if $item eq $YEAR}selected{/if} >{$item}</option>
						{/foreach}
						</select>
					</span>
				</td>
			</tr>	
			<tr class="summaryViewEntries" >
				<td class="fieldValue" colspan="2" style="padding-top:15px; padding-bottom:0px;">
					{if $YEARS }
					{vtranslate('LBL_Used_Entitled',$MODULE_NAME)} &nbsp &nbsp &nbsp 
					<span id="workDay"><strong>{$HOLIDAY}</strong></span>&nbsp 
					{vtranslate('LBL_DAYS',$MODULE_NAME)}&nbsp{vtranslate('/',$MODULE_NAME)}&nbsp 
					<span id="annual_holiday_entitlement"><strong>{$HOLIDAY_ENTITLEMENT}</strong></span> &nbsp 
					{vtranslate('LBL_DAYS',$MODULE_NAME)}
					{else}
						{vtranslate('Brak danych',$MODULE_NAME)}
					{/if}
				</td>
			</tr>
	</tbody>
</table>

{/strip}
