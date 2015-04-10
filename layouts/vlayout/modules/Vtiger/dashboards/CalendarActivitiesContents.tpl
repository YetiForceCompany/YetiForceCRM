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
<style type="text/css">
small.small-a{
font-size: 75%;
}
</style>
<div>
	{foreach from=$ACTIVITIES key=INDEX item=ACTIVITY}
	<div>
		<div class='pull-left'>
			{if $ACTIVITY->get('activitytype') == 'Task'}
				<image style="margin-left: 4px;" src="{vimage_path('Tasks.png')}" width="24px"/>&nbsp;&nbsp;
			{elseif $ACTIVITY->get('activitytype') == 'Meeting'}
				<image style="margin-left: 4px;" src="{vimage_path('Meeting.png')}" width="24px" />&nbsp;&nbsp;
			{else}
				<image style="margin-left: 4px;" src="{vimage_path('Call.png')}" width="24px" />&nbsp;&nbsp;
			{/if}
		</div>
		<div>
			<div class='pull-left'>
				{assign var=LINK value=$ACTIVITY->get('link')}
				{assign var=PROCESS value=$ACTIVITY->get('process')}
				{assign var=CONTRACTOR value=$ACTIVITY->get('contractor')}					
				<a href="{$ACTIVITY->getDetailViewUrl()}">{$ACTIVITY->get('subject')|html_entity_decode:$smarty.const.ENT_QUOTES:'utf-8'|truncate:$NAMELENGHT:'...'}</a>				
				{if $CONTRACTOR}
				    <br/><small class='small-a'>{vtranslate('LBL_FOR')} <b>{$ACTIVITY->getDisplayValue('contractor')}</b></small>, <b><small class='small-a'><a href="{$CONTRACTOR->getDetailViewUrl()}">{$CONTRACTOR->getDisplayName()|truncate:$HREFNAMELENGHT}</a></small></b>			
				{else if $LINK}
				    <br/><small class='small-a'>{vtranslate('LBL_FOR')} <b>{$ACTIVITY->getDisplayValue('link')}</b></small>
				{else if $PROCESS}
					<br/><small class='small-a'>{vtranslate('LBL_FOR')} <b>{$ACTIVITY->getDisplayValue('process')}</b></small>
				{/if}
			</div>
				{assign var=START_DATE value=$ACTIVITY->get('date_start')}
				{assign var=START_TIME value=$ACTIVITY->get('time_start')}
				
				{assign var=DUE_DATE value=$ACTIVITY->get('due_date')}
				{assign var=DUE_TIME value=$ACTIVITY->get('time_end')}
			<p class='pull-right muted' style='margin-top:5px;padding-right:5px;'><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString("$START_DATE $START_TIME")} {vtranslate('LBL_ACTIVITY_TO')} {Vtiger_Util_Helper::formatDateTimeIntoDayString("$DUE_DATE $DUE_TIME")}">{Vtiger_Util_Helper::formatDateDiffInStrings("$START_DATE $START_TIME")}</small></p>
			{if $ACTIVITY->get('location') neq '' }
				<a target="_blank" href="https://www.google.com/maps/search/{urlencode ($ACTIVITY->get('location'))}" class="pull-right" title="{vtranslate('Location', 'Calendar')}: {$ACTIVITY->get('location')}">
					<i class="icon-map-marker"></i>&nbsp
				</a>
			{/if}
			<div class='clearfix'></div>
		</div>
		<div class='clearfix'></div>
	</div>
	{foreachelse}
		<span class="noDataMsg">
			{vtranslate($NODATAMSGLABLE, $MODULE_NAME)}
		</span>
	{/foreach}
	{if $PAGING->get('nextPageExists') eq 'true'}
		<div class='pull-right' style='margin-top:5px;padding-right:5px;'>
			<a href="javascript:;" name="history_more" data-url="{$WIDGET->getUrl()}&page={$PAGING->getNextPage()}">{vtranslate('LBL_MORE')}...</a>
			<br />
			<br />
			<br />
			<br />
		</div>
	{else}
		<br />
		<br />
		<br />
		<br />
	{/if}
</div>