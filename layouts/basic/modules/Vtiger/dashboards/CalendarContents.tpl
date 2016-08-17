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
<input type="hidden" id="currentView" value="{$VIEW}" />
<input type="hidden" id="activity_view" value="{$CURRENT_USER->get('activity_view')}" />
<input type="hidden" id="time_format" value="{$CURRENT_USER->get('hour_format')}" />
<input type="hidden" id="start_hour" value="{$CURRENT_USER->get('start_hour')}" />
<input type="hidden" id="end_hour" value="{$CURRENT_USER->get('end_hour')}" />
<input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}" />
<input type="hidden" id="defaultDate" value="{$DEFAULTDATE}" />
<style>
{foreach from=Settings_Calendar_Module_Model::getCalendarConfig('colors') item=ITEM}
	.calCol_{$ITEM.label}{ background: {$ITEM.value}!important; }
{/foreach}

{foreach from=Vtiger_Module_Model::getAll() item=MODULE}
	.modIcon_{$MODULE->get('name')}{ background-image: url("{Yeti_Layout::getLayoutFile('skins/images/'|cat:$MODULE->get('name')|cat:'.png')}"); }
{/foreach}
</style>
<div class="paddingLR10">
	<div class="row">
		<div class="col-md-12">
			<div id="calendarview"></div>
		</div>
	</div>
</div>
{/strip}
