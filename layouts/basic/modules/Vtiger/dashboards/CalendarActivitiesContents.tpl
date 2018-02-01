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
		<div class="changeActivity cursorPointer" data-url="{$ACTIVITY->getActivityStateModalUrl()}" accesskey="">
			<div class="rowActivities">
			<div>
				<div class="float-left marginLeft5 marginTop5">
					{assign var=ACTIVITY_TYPE value=$ACTIVITY->get('activitytype')}
					{assign var=ACTIVITY_UPPERCASE value=$ACTIVITY_TYPE|upper}
					<image src="{Vtiger_Theme::getOrignOrDefaultImgPath($ACTIVITY_TYPE, 'Calendar')}" alt="{\App\Language::translate("LBL_$ACTIVITY_UPPERCASE")}" width="24px" />&nbsp;&nbsp;
				</div>
				{assign var=START_DATE value=$ACTIVITY->get('date_start')}
				{assign var=START_TIME value=$ACTIVITY->get('time_start')}
				
				{assign var=DUE_DATE value=$ACTIVITY->get('due_date')}
				{assign var=DUE_TIME value=$ACTIVITY->get('time_end')}
				<p class="float-right muted paddingLR10 marginTop5">
					<small>
						{\App\Fields\DateTime::formatToViewDate("$DUE_DATE $DUE_TIME")}
					</small>
				</p>
				{assign var=LINK value=$ACTIVITY->get('link')}
				{assign var=PROCESS value=$ACTIVITY->get('process')}
				{assign var=SUBPROCESS value=$ACTIVITY->get('subprocess')}
				{assign var=CONTRACTOR value=$ACTIVITY->get('contractor')}
				<div class="activityContainer">
					{$ACTIVITY->getDisplayName('subject')|truncate:$NAMELENGTH:'...'}				
					{if $CONTRACTOR}
						<br /><small class="small-a">{\App\Language::translate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('contractor')}</strong></small>, <strong><small class='small-a'><a href="{$CONTRACTOR->getDetailViewUrl()}">{$CONTRACTOR->getDisplayName()|truncate:$HREFNAMELENGTH}</a></small></strong>			
					{/if}
					{if $LINK}
						<br /><small class="small-a">{\App\Language::translate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('link')}</strong></small>
					{/if}
					{if $PROCESS}
						<br /><small class="small-a">{\App\Language::translate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('process')}</strong></small>
					{/if}
					{if $SUBPROCESS}
						<br /><small class="small-a">{\App\Language::translate('LBL_FOR')}&nbsp;<strong>{$ACTIVITY->getDisplayValue('subprocess')}</strong></small>
					{/if}
				</div>
			</div>
			{if $ACTIVITY->get('location') neq '' }
				<a target="_blank" rel="noreferrer" href="https://www.google.com/maps/search/{urlencode ($ACTIVITY->getDisplayValue('location'))}" class="float-right" title="{\App\Language::translate('Location', 'Calendar')}: {$ACTIVITY->getDisplayValue('location')}">
					<span class="icon-map-marker"></span>&nbsp
				</a>
			{/if}
			<div class="clearfix"></div>
		</div>
		<div class="clearfix"></div>
	</div>
	{foreachelse}
		<span class="noDataMsg">
			{\App\Language::translate($NODATAMSGLABLE, $MODULE_NAME)}
		</span>
	{/foreach}
	{if $PAGING_MODEL->get('nextPageExists') eq 'true'}
		<div class="float-right padding5">
			<button type="button" class="btn btn-sm btn-primary showMoreHistory" data-url="{$WIDGET->getUrl()}&page={$PAGING_MODEL->getNextPage()}">
				{\App\Language::translate('LBL_MORE')}
			</button>
		</div>
	{/if}
</div>
{/strip}
