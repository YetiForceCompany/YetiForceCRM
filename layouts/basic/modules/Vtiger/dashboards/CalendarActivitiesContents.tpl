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
<style type="text/css">
small.small-a{
font-size: 75%;
}
</style>
<div>
	{foreach from=$ACTIVITIES key=INDEX item=ACTIVITY}
		<div class="changeActivity cursorPointer" data-url="{$ACTIVITY->getActivityStateModalUrl()}" accesskey=""
			{if !empty($COLOR_LIST[$ACTIVITY->getId()])}
				style="background: {$COLOR_LIST[$ACTIVITY->getId()]['background']}; color: {$COLOR_LIST[$ACTIVITY->getId()]['text']}"
			{/if}>
			<div class="rowActivities">
			<div>
				<div class="pull-left marginLeft5 marginTop5">
					{assign var=ACTIVITY_TYPE value=$ACTIVITY->get('activitytype')}
					{assign var=ACTIVITY_UPPERCASE value=$ACTIVITY_TYPE|upper}
					<image src="{vimage_path_default($ACTIVITY_TYPE, Calendar)}" alt="{vtranslate("LBL_$ACTIVITY_UPPERCASE")}" width="24px" />&nbsp;&nbsp;
				</div>
				{assign var=START_DATE value=$ACTIVITY->get('date_start')}
				{assign var=START_TIME value=$ACTIVITY->get('time_start')}
				
				{assign var=DUE_DATE value=$ACTIVITY->get('due_date')}
				{assign var=DUE_TIME value=$ACTIVITY->get('time_end')}
				<p class="pull-right muted paddingLR10 marginTop5">
					<small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString("$START_DATE $START_TIME")} {vtranslate('LBL_ACTIVITY_TO')} {Vtiger_Util_Helper::formatDateTimeIntoDayString("$DUE_DATE $DUE_TIME")}">
						{Vtiger_Util_Helper::formatDateDiffInStrings("$DUE_DATE $DUE_TIME")}
					</small>
				</p>
				{assign var=LINK value=$ACTIVITY->get('link')}
				{assign var=PROCESS value=$ACTIVITY->get('process')}
				{assign var=SUBPROCESS value=$ACTIVITY->get('subprocess')}
				{assign var=CONTRACTOR value=$ACTIVITY->get('contractor')}
				<div class="activityContainer">
					{$ACTIVITY->get('subject')|html_entity_decode:$smarty.const.ENT_QUOTES:'utf-8'|truncate:$NAMELENGHT:'...'}				
					{if $CONTRACTOR}
						<br/><small class="small-a">{vtranslate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('contractor')}</strong></small>, <strong><small class='small-a'><a href="{$CONTRACTOR->getDetailViewUrl()}">{$CONTRACTOR->getDisplayName()|truncate:$HREFNAMELENGHT}</a></small></strong>			
					{/if}
					{if $LINK}
						<br/><small class="small-a">{vtranslate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('link')}</strong></small>
					{/if}
					{if $PROCESS}
						<br/><small class="small-a">{vtranslate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('process')}</strong></small>
					{/if}
					{if $SUBPROCESS}
						<br/><small class="small-a">{vtranslate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('subprocess')}</strong></small>
					{/if}
				</div>
			</div>
			{if $ACTIVITY->get('location') neq '' }
				<a target="_blank" href="https://www.google.com/maps/search/{urlencode ($ACTIVITY->get('location'))}" class="pull-right" title="{vtranslate('Location', 'Calendar')}: {$ACTIVITY->get('location')}">
					<span class="icon-map-marker"></span>&nbsp
				</a>
			{/if}
			<div class="clearfix"></div>
		</div>
		<div class="clearfix"></div>
	</div>
	{foreachelse}
		<span class="noDataMsg">
			{vtranslate($NODATAMSGLABLE, $MODULE_NAME)}
		</span>
	{/foreach}
	{if $PAGING_MODEL->get('nextPageExists') eq 'true'}
		<div class="pull-right padding5">
			<button type="button" class="btn btn-xs btn-primary showMoreHistory" data-url="{$WIDGET->getUrl()}&page={$PAGING_MODEL->getNextPage()}">
				{vtranslate('LBL_MORE')}
			</button>
		</div>
	{/if}
</div>
{/strip}
