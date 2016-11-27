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
	<input class="widgetData" type="hidden" value='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($DATA))}' />
	<div class="widgetDescContainer" style="margin-left: 10px;">
		<h4>Usługa: {$KPILIST[$DSERVICE]}</h4>
		<h4>Typ: {$KPITYPES[$DTYPE]}</h4>
		Przedział czasu: {$DTIME['start']} - {$DTIME['end']}<br />
		Wartość referencyjna: {$DATA['reference_lable']}<br />
		Toleranacja: {$DATA['tolerance']}<br />
		Ilość dancyh: {$DATA['all']}<br /><br />
		<h5>{$DATA['result_lable']}</h5>
	</div>
	<div class="widgetChartContainer" style="height:90px;width:90%"></div>
{else}
	<span class="noDataMsg">
		{vtranslate('LBL_NO_DATA', $MODULE_NAME)}
	</span>
{/if}
{/strip}
