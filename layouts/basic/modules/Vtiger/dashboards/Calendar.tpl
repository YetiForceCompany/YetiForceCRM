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
{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-xs-8">
			<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(),$MODULE_NAME)}</strong></div>
		</div>
		<div class="col-xs-4">
			<div class="box pull-right">
				{if Users_Privileges_Model::isPermitted('Calendar', 'CreateView')}
					<a class="btn btn-default btn-xs" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('Calendar');
							return false;">
						<span class='glyphicon glyphicon-plus' border='0' title="{vtranslate('LBL_ADD_RECORD')}" alt="{vtranslate('LBL_ADD_RECORD')}"></span>
					</a>
				{/if}
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</div>
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row" >
		<div class="col-sm-6">
			{if AppConfig::module('Calendar','DASHBOARD_CALENDAR_WIDGET_FILTER_TYPE') == 'list'}
				<div class="input-group input-group-sm">
					<span class="input-group-addon"><span class="glyphicon glyphicon-filter iconMiddle margintop3"></span></span>
					<select class="widgetFilter form-control customFilter input-sm" name="customFilter" title="{vtranslate('LBL_CUSTOM_FILTER')}">
						{assign var=CUSTOM_VIEWS value=CustomView_Record_Model::getAllByGroup('Calendar')}
						{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
							<optgroup label='{vtranslate('LBL_CV_GROUP_'|cat:strtoupper($GROUP_LABEL))}' >
								{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS} 
									<option value="{$CUSTOM_VIEW->get('cvid')}" {if $DATA['customFilter'] eq $CUSTOM_VIEW->get('cvid')} selected {/if}>{vtranslate($CUSTOM_VIEW->get('viewname'), 'Calendar')}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
			{/if}
			{if AppConfig::module('Calendar','DASHBOARD_CALENDAR_WIDGET_FILTER_TYPE') == 'switch'}
				{assign var=CURRENT_STATUS value=Calendar_Module_Model::getComponentActivityStateLabel('current')}
				{assign var=HISTORY_STATUS value=Calendar_Module_Model::getComponentActivityStateLabel('history')}
				<input class="switchBtn" type="checkbox" checked data-size="small" data-handle-width="90" data-label-width="5" data-on-text="{vtranslate('LBL_TO_REALIZE')}" data-off-text="{vtranslate('History')}"></span>
				<input type="hidden" value="current" data-current="{implode(',',$CURRENT_STATUS)}" data-history="{implode(',',$HISTORY_STATUS)}" class="widgetFilterSwitch">
			{/if}
		</div>
		<div class="col-sm-6">
			{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
		</div>
	</div>
	<div class="row marginTop2">
		<div class="col-sm-12">
			<div class="headerCalendar pinUnpinShortCut row" >
				<div class="col-xs-2">
					<button class="btn btn-default btn-sm" data-type="fc-prev-button"><span class="glyphicon glyphicon-chevron-left"></span></button>
				</div>
				<div class="col-xs-8 month textAlignCenter paddingRightZero"> </div>
				<div class="col-xs-2">
					<button class="btn btn-default btn-sm  pull-right" data-type="fc-next-button"><span class="glyphicon glyphicon-chevron-right"></span></button>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent dashboardWidgetCalendar">
	{include file="dashboards/CalendarContents.tpl"|@vtemplate_path:$MODULE_NAME WIDGET=$WIDGET}
</div>
{/strip}
