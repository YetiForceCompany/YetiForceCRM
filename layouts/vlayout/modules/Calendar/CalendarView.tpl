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
<input type="hidden" id="showType" value="current" />
<input type="hidden" id="eventLimit" value="{$EVENT_LIMIT}" />
<input type="hidden" id="weekView" value="{$WEEK_VIEW}" />
<input type="hidden" id="dayView" value="{$DAY_VIEW}" />
<style>
{foreach from=Settings_Calendar_Module_Model::getCalendarConfig('colors') item=ITEM}
	.calCol_{$ITEM.label}{ border: 1px solid {$ITEM.value}!important; }
	.listCol_{$ITEM.label}{ background: {$ITEM.value}!important; }
{/foreach}
{foreach from=Settings_Calendar_Module_Model::getUserColors('colors') item=ITEM}
	.userCol_{$ITEM.id}{ background: {$ITEM.color}!important; }
{/foreach}
{foreach from=Vtiger_Module_Model::getAll() item=MODULE}
	.modIcon_{$MODULE->get('name')}{ background-image: url("layouts/vlayout/skins/images/{$MODULE->get('name')}.png"); }
{/foreach}
</style>
<div class="calendarViewContainer">
	<div class="bottom_margin">
		<div class="">
			<p><!-- Divider --></p>
			<div id="calendarview"></div>
		</div>
	</div>
</div>
<div class="btn-group listViewMassActions hide">
	{if count($QUICK_LINKS['SIDEBARLINK']) gt 0}
		<button class="btn btn-default fc-button fc-state-default dropdown-toggle" data-toggle="dropdown">
			<span class="glyphicon glyphicon-list" aria-hidden="true"></span>
			&nbsp;&nbsp;<span class="caret"></span>
		</button>
		<ul class="dropdown-menu">
			{foreach item=SIDEBARLINK from=$QUICK_LINKS['SIDEBARLINK']}
				<li>
					<a class="quickLinks" href="{$SIDEBARLINK->getUrl()}">
						{vtranslate($SIDEBARLINK->getLabel(), $MODULE_NAME)}
					</a>
				</li>
			{/foreach}
		</ul>
	{/if}
</div>
{/strip}
